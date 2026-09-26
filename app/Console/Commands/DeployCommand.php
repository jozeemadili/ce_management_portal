<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * php artisan deploy - pulls the Git working copy, synchronises it to the
 * LIVE application with rsync, then fixes permissions, rebuilds caches, runs
 * migrations, checks the storage link and restarts PHP-FPM / Nginx.
 *
 * Every external command runs through Symfony Process with an argument
 * array (no shell, no string concatenation); paths come from
 * config/deployment.php, never from user input. Root-only operations go
 * through ONE root-owned helper allowed by a narrow sudoers rule, called
 * with `sudo -n` so a missing rule fails fast instead of waiting for a
 * password. See DEPLOYMENT.md.
 */
class DeployCommand extends Command
{
    protected $signature = 'deploy
        {--force : Skip the confirmation prompt (for cron / CI)}
        {--dry-run : Show what would happen - change nothing on LIVE}
        {--no-pull : Deploy the commit already checked out in the Git working copy (used for rollback)}
        {--no-restart : Do not restart PHP-FPM and Nginx}
        {--no-migrate : Do not run database migrations}
        {--no-cache : Do not clear or rebuild Laravel caches}
        {--no-permissions : Do not fix storage/ and bootstrap/cache permissions}
        {--no-storage-link : Do not check/create the public/storage link}';

    protected $description = 'Pull latest code and deploy the Laravel application';

    private const EXIT_FAILED = 1;
    private const EXIT_LOCKED = 2;

    private array $cfg;
    private bool $dryRun = false;
    private string $currentStep = 'Start';
    private int $stepNumber = 0;
    private int $stepTotal = 8;

    /** @var resource|null */
    private $lockHandle = null;

    /** Collected for the deployment log. */
    private array $report = [];

    public function handle(): int
    {
        $this->cfg = config('deployment') ?? [];
        if (empty($this->cfg['git_path']) || empty($this->cfg['live_path'])) {
            // Typically the first run after config/deployment.php arrived on a
            // server whose configuration is cached.
            $this->error('Deployment configuration not loaded (config/deployment.php).');
            $this->line('If the configuration is cached, run: php artisan config:clear');

            return self::EXIT_FAILED;
        }
        $this->dryRun = (bool) $this->option('dry-run');

        $this->banner('Christ Embassy Portal Deployment');
        if ($this->dryRun) {
            $this->warn('DRY RUN - NO CHANGES WILL BE MADE');
            $this->newLine();
        }

        if (!$this->acquireLock()) {
            $this->error('Deployment already in progress.');
            $this->line('Lock file: ' . $this->cfg['lock_file']);

            return self::EXIT_LOCKED;
        }

        $this->report = [
            'user' => $this->currentUser(),
            'dry_run' => $this->dryRun,
            'options' => collect($this->options())->filter(fn ($v) => $v === true)->keys()->values()->all(),
        ];
        $this->log('Deployment started', $this->report);

        try {
            $restartOk = $this->runDeployment();
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        } finally {
            $this->releaseLock();
        }

        if ($this->dryRun) {
            $this->banner('DRY RUN COMPLETED - NOTHING WAS CHANGED');
            $this->log('Dry run completed', $this->report);

            return self::SUCCESS;
        }

        if (!$restartOk) {
            $this->banner('DEPLOYMENT COMPLETED - SERVICE RESTART FAILED', 'error');
            $this->line('The new code is live, but a service did not restart. See the messages above.');
            $this->log('Deployment finished with service restart failure', $this->report, 'error');

            return self::EXIT_FAILED;
        }

        $this->banner('DEPLOYMENT COMPLETED SUCCESSFULLY');
        $this->log('Deployment completed successfully', $this->report);

        return self::SUCCESS;
    }

    /**
     * @return bool whether services restarted fine (or were skipped)
     */
    private function runDeployment(): bool
    {
        $this->step('Checking environment');
        $this->checkEnvironment();
        $this->ok('Environment OK');

        if (!$this->dryRun && !$this->confirmDeployment()) {
            throw new RuntimeException('Cancelled by user - nothing was changed.');
        }

        $this->step('Pulling latest Git changes');
        $this->updateGit();

        $this->step('Synchronizing application');
        $this->syncToLive();

        $this->step('Fixing Laravel permissions');
        $this->fixPermissions();

        $this->step('Clearing Laravel cache');
        $this->rebuildCaches();

        $this->step('Running database migrations');
        $this->migrate();

        $this->step('Checking storage link');
        $this->checkStorageLink();

        $this->step('Restarting services');

        return $this->restartServices();
    }

    /* ------------------------------------------------------------------
     | 1. Environment
     |------------------------------------------------------------------*/

    private function checkEnvironment(): void
    {
        $git = $this->path('git_path');
        $live = $this->path('live_path');

        foreach (['git', 'rsync'] as $binary) {
            if (!(new ExecutableFinder())->find($binary)) {
                throw new RuntimeException("'{$binary}' is not installed or not on PATH.");
            }
        }

        if (!is_dir($git)) {
            throw new RuntimeException("Git working copy not found: {$git}");
        }
        if (!is_dir($live)) {
            throw new RuntimeException("LIVE application not found: {$live}");
        }
        if (realpath($git) === realpath($live)) {
            throw new RuntimeException('The Git working copy and LIVE are the same directory - refusing to deploy.');
        }
        if (!is_dir($git . '/.git')) {
            throw new RuntimeException("{$git} is not a Git repository.");
        }
        foreach (['artisan', 'composer.json'] as $file) {
            if (!is_file($live . '/' . $file)) {
                throw new RuntimeException("LIVE is not a Laravel application: {$live}/{$file} is missing.");
            }
            if (!is_file($git . '/' . $file)) {
                throw new RuntimeException("The Git working copy is not this Laravel application: {$git}/{$file} is missing.");
            }
        }
        if (!is_dir($git . '/vendor')) {
            $this->warn('  ! vendor/ is not in the Git working copy - LIVE vendor/ would be deleted by --delete.');
            throw new RuntimeException('vendor/ missing from the Git working copy. It is committed in this project; check the checkout.');
        }

        // This command is meant to run from the LIVE application.
        if (realpath(base_path()) !== realpath($live)) {
            $message = 'This command is running from ' . base_path() . ", not from LIVE ({$live}).";
            if (!$this->dryRun) {
                throw new RuntimeException($message . ' Run it from the LIVE application.');
            }
            $this->warn('  ! ' . $message);
        }

        $this->line('  User:        ' . $this->report['user']);
        $this->line("  Git copy:    {$git} ({$this->cfg['remote']}/{$this->cfg['branch']})");
        $this->line("  LIVE:        {$live}");

        // rsync without sudo needs every LIVE file (outside protected paths)
        // to be writable by this user.
        if (!$this->cfg['rsync_use_sudo']) {
            $blocked = $this->firstNotWritable($live);
            if ($blocked !== null) {
                $message = "Not writable by {$this->report['user']}: {$blocked}. "
                    . 'Give the deploy user ownership of the LIVE code (see DEPLOYMENT.md, "Permissions") '
                    . 'or set DEPLOY_RSYNC_USE_SUDO=true.';
                if (!$this->dryRun) {
                    throw new RuntimeException($message);
                }
                $this->warn('  ! ' . $message);
            } else {
                $this->line('  rsync:       as ' . $this->report['user'] . ' (no sudo needed)');
            }
        } else {
            $this->line('  rsync:       through the sudo helper');
        }

        // Anything that needs root goes through the helper; check it now,
        // before a single file changes, so a missing sudo rule never stops a
        // deployment half-way.
        $needsSudo = $this->cfg['rsync_use_sudo'] || !$this->option('no-permissions') || !$this->option('no-restart');
        if ($needsSudo) {
            $helper = $this->helperInfo();
            if ($helper === null) {
                $message = 'The sudo helper is not usable without a password: ' . $this->cfg['sudo_helper']
                    . '. Install it with scripts/deploy/install-deploy-helper.sh (see DEPLOYMENT.md), '
                    . 'or deploy with --no-permissions --no-restart.';
                if (!$this->dryRun) {
                    throw new RuntimeException($message);
                }
                $this->warn('  ! ' . $message);
            } else {
                foreach (['live' => $live, 'git' => $git] as $key => $expected) {
                    if (isset($helper[$key]) && realpath($helper[$key]) !== realpath($expected)) {
                        throw new RuntimeException("The sudo helper was installed for {$key} path {$helper[$key]}, but config says {$expected}. Re-run the installer.");
                    }
                }
                $this->line('  sudo helper: OK (no password) - PHP-FPM service: ' . ($helper['php_fpm'] ?? '?'));
                $this->report['php_fpm_service'] = $helper['php_fpm'] ?? null;
            }
        }

        $this->newLine();
        $this->warn('  rsync runs with --delete: files on LIVE that are not in Git are DELETED.');
        $this->line('  Protected (never copied or deleted): ' . implode(', ', $this->cfg['protected_paths']));
        $this->line('  vendor/ IS synchronised from Git.');
    }

    private function confirmDeployment(): bool
    {
        if ($this->option('force')) {
            $this->line('  --force: skipping confirmation.');

            return true;
        }
        $noTerminal = function_exists('posix_isatty') && !@posix_isatty(STDIN);
        if (!$this->input->isInteractive() || $noTerminal) {
            throw new RuntimeException('No terminal to confirm on (cron/CI?) - nothing was changed. Use --force to deploy without confirmation.');
        }

        return $this->confirm('Are you sure you want to deploy to production?', false);
    }

    /* ------------------------------------------------------------------
     | 2. Git
     |------------------------------------------------------------------*/

    private function updateGit(): void
    {
        $git = $this->path('git_path');
        $branch = $this->cfg['branch'];
        $remote = $this->cfg['remote'];

        $old = $this->gitHead();
        $this->report['commit_before'] = $old;

        // Uncommitted edits in the working copy would be copied to LIVE.
        $dirty = trim($this->mustRun(['git', 'status', '--porcelain', '--untracked-files=no'], $git, 'git')->getOutput());
        if ($dirty !== '') {
            throw new RuntimeException("The Git working copy has uncommitted changes - refusing to deploy them:\n{$dirty}");
        }

        if ($this->option('no-pull')) {
            $this->ok('Git pull skipped (--no-pull) - deploying the checked-out commit');
            $this->line('  Commit: ' . $this->describeCommit($old));
            $this->report['commit_after'] = $old;

            return;
        }

        if (!$this->process(['git', 'rev-parse', '--verify', '--quiet', "refs/heads/{$branch}"], $git, 'git')->isSuccessful()) {
            throw new RuntimeException("Branch '{$branch}' does not exist in the Git working copy.");
        }

        if ($this->dryRun) {
            // Fetch only: updates remote-tracking refs in the Git copy, not
            // its files, and nothing on LIVE.
            $this->mustRun(['git', 'fetch', $remote, $branch], $git, 'git');
            $incoming = trim($this->mustRun(['git', 'log', '--oneline', "HEAD..{$remote}/{$branch}"], $git, 'git')->getOutput());

            $this->ok('Git checked (fetch only)');
            $this->line('  Current commit: ' . $this->describeCommit($old));
            if ($incoming === '') {
                $this->line('  No new Git commits found.');
            } else {
                $this->line('  Commits a real deployment would pull:');
                foreach (explode("\n", $incoming) as $line) {
                    $this->line('    ' . $line);
                }
                $this->line('  Files they change:');
                $files = trim($this->mustRun(['git', 'diff', '--name-status', "HEAD", "{$remote}/{$branch}"], $git, 'git')->getOutput());
                foreach (explode("\n", $files) as $line) {
                    $this->line('    ' . $line);
                }
                $this->warn('  Note: the rsync preview below uses the CURRENT checkout (before these commits).');
            }
            $this->report['commit_after'] = $old;

            return;
        }

        $this->mustRun(['git', 'checkout', $branch], $git, 'git');
        // --ff-only: never create a merge commit on the server; a diverged
        // working copy fails loudly instead.
        $this->mustRun(['git', 'pull', '--ff-only', $remote, $branch], $git, 'git');

        $new = $this->gitHead();
        $this->report['commit_after'] = $new;

        $this->ok('Git updated');
        $this->line('  Old commit: ' . $this->describeCommit($old));
        $this->line('  New commit: ' . $this->describeCommit($new));
        if ($old === $new) {
            $this->line('  No new Git commits found.');
        }
    }

    private function gitHead(): string
    {
        return trim($this->mustRun(['git', 'rev-parse', 'HEAD'], $this->path('git_path'), 'git')->getOutput());
    }

    private function describeCommit(string $sha): string
    {
        $subject = trim($this->process(['git', 'log', '-1', '--format=%s', $sha], $this->path('git_path'), 'git')->getOutput());

        return substr($sha, 0, 7) . ($subject !== '' ? " ({$subject})" : '');
    }

    /* ------------------------------------------------------------------
     | 3. rsync
     |------------------------------------------------------------------*/

    private function syncToLive(): void
    {
        if ($this->cfg['rsync_use_sudo']) {
            $command = ['sudo', '-n', $this->cfg['sudo_helper'], 'sync'];
            if ($this->dryRun) {
                $command[] = '--dry-run';
            }
        } else {
            $command = ['rsync', '-av', '--delete'];
            if ($this->dryRun) {
                $command[] = '--dry-run';
            }
            foreach ($this->cfg['protected_paths'] as $path) {
                $command[] = '--exclude=' . $path;
            }
            $command[] = rtrim($this->path('git_path'), '/') . '/';
            $command[] = rtrim($this->path('live_path'), '/') . '/';
        }

        $process = $this->process($command, null, $this->cfg['rsync_use_sudo'] ? 'sudo' : 'rsync');
        if (!$process->isSuccessful()) {
            throw new RuntimeException('rsync failed (exit ' . $process->getExitCode() . "):\n" . $this->tail($process->getErrorOutput() ?: $process->getOutput()));
        }

        $lines = array_filter(array_map('trim', explode("\n", $process->getOutput())));
        $deleted = array_values(array_filter($lines, fn ($l) => str_starts_with($l, 'deleting ')));
        $changed = array_values(array_filter($lines, fn ($l) => !str_starts_with($l, 'deleting ')
            && !str_starts_with($l, 'sending incremental') && !str_starts_with($l, 'sent ')
            && !str_starts_with($l, 'total size') && !str_ends_with($l, '/') && !str_contains($l, 'DRY RUN')));

        $this->report['rsync'] = ['changed' => count($changed), 'deleted' => count($deleted)];

        $this->ok($this->dryRun ? 'rsync dry run completed' : 'Rsync completed');
        $this->line('  Files ' . ($this->dryRun ? 'that would be ' : '') . 'updated: ' . count($changed));
        $this->line('  Files ' . ($this->dryRun ? 'that would be ' : '') . 'deleted from LIVE: ' . count($deleted));

        if ($this->dryRun || $this->output->isVerbose()) {
            foreach (array_slice($changed, 0, 200) as $line) {
                $this->line('    ~ ' . $line);
            }
            if (count($changed) > 200) {
                $this->line('    ... and ' . (count($changed) - 200) . ' more');
            }
        }
        foreach ($deleted as $line) {
            $this->warn('    - ' . substr($line, strlen('deleting ')));
        }
    }

    /* ------------------------------------------------------------------
     | 4. Permissions
     |------------------------------------------------------------------*/

    private function fixPermissions(): void
    {
        if ($this->option('no-permissions')) {
            $this->skip('Permissions skipped (--no-permissions)');

            return;
        }
        if ($this->dryRun) {
            $this->skip('Would run: sudo ' . $this->cfg['sudo_helper'] . ' fix-permissions ('
                . implode(', ', $this->cfg['writable_paths']) . ')');

            return;
        }

        $this->mustRun(['sudo', '-n', $this->cfg['sudo_helper'], 'fix-permissions'], null, 'sudo');
        $this->report['permissions'] = 'ok';
        $this->ok('Permissions updated (' . implode(', ', $this->cfg['writable_paths']) . ')');
    }

    /* ------------------------------------------------------------------
     | 5. Cache
     |------------------------------------------------------------------*/

    private function rebuildCaches(): void
    {
        if ($this->option('no-cache')) {
            $this->skip('Cache skipped (--no-cache)');

            return;
        }

        $commands = [['optimize:clear']];
        foreach (['config' => 'config:cache', 'route' => 'route:cache', 'view' => 'view:cache'] as $key => $command) {
            if ($this->cfg['cache'][$key] ?? false) {
                $commands[] = [$command];
            }
        }

        if ($this->dryRun) {
            $this->skip('Would run: ' . implode(', ', array_map(fn ($c) => 'php artisan ' . $c[0], $commands)));

            return;
        }

        foreach ($commands as $command) {
            $this->artisan($command);
            $this->line('  ✓ php artisan ' . $command[0]);
        }
        $this->report['cache'] = array_column($commands, 0);
        $this->ok('Cache cleared and rebuilt');
    }

    /* ------------------------------------------------------------------
     | 6. Migrations
     |------------------------------------------------------------------*/

    private function migrate(): void
    {
        if ($this->option('no-migrate')) {
            $this->skip('Migrations skipped (--no-migrate)');

            return;
        }

        // migrate:status is read-only - also useful in a dry run. It runs in
        // the LIVE app, so in a dry run it reflects the code already live.
        $status = $this->artisan(['migrate:status'], false);
        $pending = array_values(array_filter(
            explode("\n", $status->getOutput()),
            fn ($line) => stripos($line, 'pending') !== false
        ));

        if (!$status->isSuccessful()) {
            throw new RuntimeException("php artisan migrate:status failed:\n" . $this->tail($status->getErrorOutput() ?: $status->getOutput()));
        }

        $this->line('  Pending migrations: ' . count($pending));
        foreach ($pending as $line) {
            $this->line('    ' . trim(preg_replace('/\s*\.{2,}\s*/', ' ', $line)));
        }

        if ($this->dryRun) {
            $this->skip('Would run: php artisan migrate --force');

            return;
        }

        $this->artisan(['migrate', '--force', '--no-interaction']);
        $this->report['migrations'] = count($pending);
        $this->ok(count($pending) ? 'Migrations completed' : 'Migrations completed (nothing to migrate)');
    }

    /* ------------------------------------------------------------------
     | 7. Storage link
     |------------------------------------------------------------------*/

    private function checkStorageLink(): void
    {
        if ($this->option('no-storage-link')) {
            $this->skip('Storage link skipped (--no-storage-link)');

            return;
        }

        $link = $this->path('live_path') . '/public/storage';
        $target = $this->path('live_path') . '/storage/app/public';

        if (is_link($link) && realpath($link) === realpath($target)) {
            $this->report['storage_link'] = 'ok';
            $this->ok('Storage link OK');

            return;
        }

        if (file_exists($link) && !is_link($link)) {
            // A real directory: never delete it automatically.
            $this->report['storage_link'] = 'not a symlink';
            $this->warn('  ! public/storage exists but is not a symlink - left untouched. Check it manually.');

            return;
        }

        $broken = is_link($link);
        if ($this->dryRun) {
            $this->skip('Would run: php artisan storage:link' . ($broken ? ' --force (current link is broken)' : ''));

            return;
        }

        $this->artisan($broken ? ['storage:link', '--force'] : ['storage:link']);
        $this->report['storage_link'] = $broken ? 'repaired' : 'created';
        $this->ok($broken ? 'Storage link repaired' : 'Storage link created');
    }

    /* ------------------------------------------------------------------
     | 8. Services
     |------------------------------------------------------------------*/

    private function restartServices(): bool
    {
        if ($this->option('no-restart')) {
            $this->skip('Service restart skipped (--no-restart)');

            return true;
        }

        $phpFpm = $this->cfg['php_fpm_service'] ?: ($this->report['php_fpm_service'] ?? 'PHP-FPM');
        $nginx = $this->cfg['nginx_service'];

        if ($this->dryRun) {
            $this->skip("Would restart: {$phpFpm}, {$nginx} (Nginx config is tested first)");

            return true;
        }

        $allOk = true;
        foreach (['restart-php' => $phpFpm, 'restart-nginx' => $nginx] as $action => $name) {
            $process = $this->process(['sudo', '-n', $this->cfg['sudo_helper'], $action], null, 'sudo');
            if ($process->isSuccessful()) {
                $this->ok("{$name} restarted");
                $this->report['restart'][$name] = 'ok';
            } else {
                $allOk = false;
                $this->error("  ✗ {$name} restart failed: " . $this->tail($process->getErrorOutput() ?: $process->getOutput(), 5));
                $this->report['restart'][$name] = 'failed';
            }
        }

        return $allOk;
    }

    /* ------------------------------------------------------------------
     | Helpers
     |------------------------------------------------------------------*/

    /** The helper's baked-in settings (`check`), or null if sudo needs a password / helper missing. */
    private function helperInfo(): ?array
    {
        if (!is_file($this->cfg['sudo_helper'])) {
            return null;
        }
        $process = $this->process(['sudo', '-n', $this->cfg['sudo_helper'], 'check'], null, 'sudo');
        if (!$process->isSuccessful()) {
            return null;
        }

        $info = [];
        foreach (explode("\n", trim($process->getOutput())) as $line) {
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $info[trim($key)] = trim($value);
            }
        }

        return $info;
    }

    /**
     * First file/dir in LIVE (outside protected paths) this user can't
     * write, or null. Plain PHP rather than `find -writable`, which only GNU
     * find supports - a check that silently does nothing would be worse than
     * none.
     */
    private function firstNotWritable(string $live): ?string
    {
        $live = rtrim($live, '/');
        $protected = array_map(fn ($p) => $live . '/' . rtrim($p, '/'), $this->cfg['protected_paths']);

        if (!is_writable($live)) {
            return $live;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($live, \FilesystemIterator::SKIP_DOTS),
                fn (\SplFileInfo $file) => !in_array($file->getPathname(), $protected, true)
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isLink() && !is_writable($file->getPathname())) {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function artisan(array $arguments, bool $mustSucceed = true): Process
    {
        $command = array_merge([PHP_BINARY, $this->path('live_path') . '/artisan'], $arguments, ['--no-ansi']);
        $process = $this->process($command, $this->path('live_path'), 'artisan');

        if ($mustSucceed && !$process->isSuccessful()) {
            throw new RuntimeException('php artisan ' . implode(' ', $arguments) . " failed:\n"
                . $this->tail($process->getErrorOutput() . "\n" . $process->getOutput()));
        }

        return $process;
    }

    private function process(array $command, ?string $cwd, string $timeoutKey): Process
    {
        // GIT_TERMINAL_PROMPT=0: never hang waiting for Git credentials.
        $process = new Process($command, $cwd, ['GIT_TERMINAL_PROMPT' => '0', 'LC_ALL' => 'C'], null, $this->cfg['timeouts'][$timeoutKey] ?? 300);
        $process->run();

        return $process;
    }

    private function mustRun(array $command, ?string $cwd, string $timeoutKey): Process
    {
        $process = $this->process($command, $cwd, $timeoutKey);
        if (!$process->isSuccessful()) {
            throw new RuntimeException(implode(' ', $command) . ' failed (exit ' . $process->getExitCode() . "):\n"
                . $this->tail($process->getErrorOutput() ?: $process->getOutput()));
        }

        return $process;
    }

    private function path(string $key): string
    {
        return rtrim($this->cfg[$key], '/');
    }

    private function currentUser(): string
    {
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            return posix_getpwuid(posix_geteuid())['name'] ?? (string) posix_geteuid();
        }

        return get_current_user();
    }

    private function tail(string $text, int $lines = 20): string
    {
        // Strip terminal colour codes: Laravel's error renderer adds them
        // even with --no-ansi, and they're noise in logs and cron mail.
        $text = preg_replace('/\e\[[0-9;]*[A-Za-z]/', '', $text);
        $all = array_filter(explode("\n", trim($text)), fn ($l) => trim($l) !== '');

        return implode("\n", array_slice($all, -$lines));
    }

    private function acquireLock(): bool
    {
        $handle = @fopen($this->cfg['lock_file'], 'c');
        if ($handle === false) {
            throw new RuntimeException('Cannot open lock file ' . $this->cfg['lock_file']);
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }
        ftruncate($handle, 0);
        fwrite($handle, getmypid() . ' ' . date('c') . "\n");
        $this->lockHandle = $handle;

        return true;
    }

    private function releaseLock(): void
    {
        if ($this->lockHandle) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }

    private function step(string $title): void
    {
        $this->currentStep = $title;
        $this->stepNumber++;
        $this->newLine();
        $this->info("[{$this->stepNumber}/{$this->stepTotal}] {$title}...");
    }

    private function ok(string $message): void
    {
        $this->line("<info>✓</info> {$message}");
    }

    private function skip(string $message): void
    {
        $this->line("<comment>-</comment> {$message}");
    }

    private function banner(string $title, string $style = 'info'): void
    {
        $this->newLine();
        $this->line(str_repeat('=', 40));
        $style === 'error' ? $this->error($title) : $this->info($title);
        $this->line(str_repeat('=', 40));
    }

    private function fail(string $reason): int
    {
        $this->newLine();
        $this->line(str_repeat('=', 40));
        $this->error('DEPLOYMENT FAILED');
        $this->line(str_repeat('=', 40));
        $this->newLine();
        $this->line('Step:');
        $this->line("  [{$this->stepNumber}/{$this->stepTotal}] {$this->currentStep}");
        $this->line('Reason:');
        foreach (explode("\n", $reason) as $line) {
            $this->line('  ' . $line);
        }
        $this->newLine();
        $this->line(str_repeat('=', 40));

        $this->log('Deployment failed', $this->report + ['step' => $this->currentStep, 'reason' => $this->tail($reason, 10)], 'error');

        return self::EXIT_FAILED;
    }

    private function log(string $message, array $context = [], string $level = 'info'): void
    {
        try {
            Log::channel('deployment')->{$level}($message, $context);
        } catch (Throwable $e) {
            // Logging must never break a deployment.
        }
    }
}
