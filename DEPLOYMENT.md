# Deployment — `php artisan deploy`

Production deployment for the Christ Embassy portal
(`https://event.christembassytanzania.org`).

---

## 1. Architecture

```
GitHub  jozeemadili/ce_management_portal  (branch: main)
   │  git pull --ff-only origin main
   ▼
/var/www/html/ce_management_portal_git          ← Git working copy (never served)
   │  rsync -av --delete  (protected paths excluded)
   ▼
/var/www/html/event.christembassytanzania.org    ← LIVE Laravel app (served by Nginx + PHP-FPM)
      then: permissions → caches → migrations → storage link → restart services
```

The command runs **from LIVE**:

```bash
cd /var/www/html/event.christembassytanzania.org
php artisan deploy
```

| Piece | Location |
|---|---|
| Command | `app/Console/Commands/DeployCommand.php` |
| Settings | `config/deployment.php` (no secrets; defaults match the paths above) |
| Log channel | `deployment` in `config/logging.php` → `storage/logs/deployment-YYYY-MM-DD.log`, kept 30 days |
| Root helper (template) | `scripts/deploy/ce-deploy-helper` → installed at `/usr/local/sbin/ce-deploy-helper` |
| Helper installer / remover | `scripts/deploy/install-deploy-helper.sh`, `scripts/deploy/uninstall-deploy-helper.sh` |
| Read-only server report | `scripts/deploy/inspect-server.sh` |

Every setting can be overridden in `.env` (`DEPLOY_GIT_PATH`, `DEPLOY_LIVE_PATH`, `DEPLOY_BRANCH`, `DEPLOY_REMOTE`, `DEPLOY_RSYNC_USE_SUDO`, `DEPLOY_PHP_FPM_SERVICE`, `DEPLOY_NGINX_SERVICE`, `DEPLOY_SUDO_HELPER`, `DEPLOY_LOCK_FILE`). On production the defaults are correct; nothing needs adding. If you change them, run `php artisan config:clear` (configuration is cached).

---

## 2. What a deployment does

| Step | What happens | Stops on failure? |
|---|---|---|
| 1. Check environment | `git`/`rsync` present; both directories exist and differ; the Git copy is a repository; LIVE and the Git copy both have `artisan` + `composer.json`; `vendor/` is in the Git copy; the command runs from LIVE; LIVE is writable by the deploy user (or rsync goes through sudo); the sudo helper works **without a password** and was installed for the same paths. Nothing is changed before all of this passes. | yes |
| (confirm) | `Are you sure you want to deploy to production? (yes/no)` — skipped with `--force`. Without a terminal (cron/CI) and without `--force`, it stops. | yes |
| 2. Git | Refuses if the Git copy has uncommitted changes. `git checkout main`, `git pull --ff-only origin main` (never creates a merge commit on the server). Shows old and new commit. | yes |
| 3. rsync | `rsync -av --delete` Git copy → LIVE, protected paths excluded. Lists deleted files. | yes |
| 4. Permissions | `sudo -n ce-deploy-helper fix-permissions` → `storage/` and `bootstrap/cache/`: owner = deploy user, group = web server group; directories `2775` (setgid), files `0664`. Never `777`. | yes |
| 5. Cache | `php artisan optimize:clear`, then `config:cache` and `route:cache`. `view:cache` is **off** (see §6). | yes |
| 6. Migrations | `php artisan migrate:status` (shows pending), then `php artisan migrate --force`. Never `migrate:fresh`, `db:wipe` or an automatic rollback. | yes |
| 7. Storage link | Creates `public/storage` if missing, repairs it only if it's a broken symlink, never touches a real directory. | yes |
| 8. Services | `sudo -n ce-deploy-helper restart-php` and `restart-nginx` (Nginx config is tested with `nginx -t` first). | reported; exit code 1 |

Each run holds an exclusive lock (`/tmp/ce_management_portal_deploy.lock`, `flock`), so two deployments can never overlap: the second prints `Deployment already in progress.` and exits with code **2**. The lock is released on success, failure and even a crash.

Exit codes: `0` success · `1` failed (or service restart failed) · `2` another deployment is running.

---

## 3. Commands

```bash
cd /var/www/html/event.christembassytanzania.org

php artisan deploy                      # asks for confirmation
php artisan deploy --force              # no prompt (cron / CI)
php artisan deploy --dry-run            # show what would happen, change nothing
php artisan deploy --force --no-migrate # skip migrations
php artisan deploy --force --no-cache   # skip cache clear/rebuild
php artisan deploy --force --no-restart # skip PHP-FPM / Nginx restart
php artisan deploy --force --no-permissions
php artisan deploy --force --no-storage-link
php artisan deploy --force --no-pull    # deploy the commit already checked out (rollback, §9)
php artisan deploy --help
```

Options combine freely, e.g. `php artisan deploy --force --no-restart --no-migrate`. Add `-v` to list every file rsync updates.

### Dry run

`php artisan deploy --dry-run` prints `DRY RUN - NO CHANGES WILL BE MADE` and:

- runs every environment check (problems are shown as warnings instead of stopping);
- `git fetch origin main` only — lists the commits and files a real deployment would pull (the Git copy's files are not changed);
- `rsync --dry-run` — lists what would be updated **and deleted** on LIVE (based on the commit currently checked out, i.e. before the incoming commits);
- `php artisan migrate:status` (read-only) — lists pending migrations;
- says which permission/cache/storage-link/restart actions it would take.

It does not modify LIVE files, run migrations, change permissions, touch caches or restart services. (It writes the deployment log, and — like any `artisan` command — Laravel may create its own package cache files in `bootstrap/cache` if they are missing.)

---

## 4. rsync, `--delete` and protected paths

```bash
rsync -av --delete \
  --exclude='.env' --exclude='.git/' --exclude='storage/' \
  --exclude='public/storage' --exclude='bootstrap/cache/' \
  /var/www/html/ce_management_portal_git/ /var/www/html/event.christembassytanzania.org/
```

- **`--delete`**: any file on LIVE that is not in Git is **deleted** — e.g. a file someone uploaded or edited directly on the server outside `storage/`. Check `php artisan deploy --dry-run` first if unsure; it lists every deletion.
- **Protected** (never copied from Git, never deleted on LIVE): `.env`, `.git/`, `storage/` (uploads, logs, sessions, payment proofs), `public/storage` (symlink), `bootstrap/cache/`.
- `--delete-excluded` is **not** used, so protected paths are safe.
- **`vendor/` is synchronised.** It is committed to Git in this project (≈9,900 files), so there is no `composer install` on the server; `vendor/` on LIVE always matches the commit.
- If you add a production-only file/folder outside `storage/`, add it to `protected_paths` in `config/deployment.php` **and** to the helper (reinstall) — otherwise it will be deleted.

---

## 5. Permissions and sudo (minimum privileges)

### Which operations need root

| Operation | Needs sudo? | Why |
|---|---|---|
| `git fetch/pull` in the Git copy | **No** | The deploy user owns the Git copy. |
| `rsync` Git → LIVE | **No** (recommended setup) | After the one-time `--own-live-code`, the deploy user owns the LIVE **code** files. |
| `chown`/`chmod` of `storage/`, `bootstrap/cache/` | **Yes** | PHP-FPM (`www-data`) creates files there (logs, cache, sessions, uploads) that the deploy user cannot `chmod`/`chown`. |
| `php artisan ...` (cache, migrate, storage:link) | **No** | Runs as the deploy user; writes only to group-writable paths. |
| `systemctl restart <php-fpm>` / `nginx` | **Yes** | Service control is root-only. |

### How sudo is limited

Nothing is allowed through sudo except **one root-owned script**, `/usr/local/sbin/ce-deploy-helper`, with **exact** arguments. All paths and service names are written into that script at install time — the caller cannot pass a path, user or service name. The command calls it with `sudo -n`, so a missing rule fails immediately instead of waiting for a password.

`/etc/sudoers.d/ce-deployment` (created by the installer, `root:root 0440`, validated with `visudo -cf` before activation):

```sudoers
Defaults!/usr/local/sbin/ce-deploy-helper !requiretty
josephat ALL=(root) NOPASSWD: /usr/local/sbin/ce-deploy-helper check, /usr/local/sbin/ce-deploy-helper fix-permissions, /usr/local/sbin/ce-deploy-helper restart-php, /usr/local/sbin/ce-deploy-helper restart-nginx
```

| Rule | Why |
|---|---|
| `check` | Read-only: lets `deploy` confirm, before changing anything, that sudo works without a password and the helper targets the same paths. |
| `fix-permissions` | `chown -hRP <user>:<web group>` + `2775`/`0664` on `LIVE/storage` and `LIVE/bootstrap/cache` only. |
| `restart-php` | `systemctl restart <php-fpm service detected at install>` (e.g. `php8.2-fpm`). |
| `restart-nginx` | `nginx -t`, then `systemctl restart nginx`. |
| `!requiretty` | Lets cron/CI (no terminal) use these rules. |

With `--with-sync` the installer also adds `ce-deploy-helper sync` and `sync --dry-run` (rsync as root). Use that **only** if the deploy user cannot own the LIVE code; set `DEPLOY_RSYNC_USE_SUDO=true` in `.env` then.

Not granted: general sudo, `sudo rsync`, `sudo chown`, `sudo chmod`, `sudo systemctl`, or anything with free-form arguments.

---

## 6. Caching (inspected for this project)

- `config:cache` — **on**. `env()` is only called inside `config/`, and it succeeds.
- `route:cache` — **on**. It succeeds (note: there are duplicate route names — `login`, `forget-password`, `security-system-configurations` — and `routes/web.php` references an `InvoiceController` that isn't imported; they don't break caching but are worth cleaning up).
- `view:cache` — **off**. It fails on the unused leftover view `resources/views/livewire/datatables/filters/editable.blade.php` (component `icons.x-circle` does not exist). Views still compile on first use. Fix or remove that view, then set `'view' => true` in `config/deployment.php`.

---

## 7. First-time setup (once, on the server)

1. **Get these files onto the server.** Commit and push them, then — the last time — do the manual update:
   ```bash
   cd /var/www/html/ce_management_portal_git && git pull origin main
   sudo rsync -av --delete --exclude='.env' --exclude='.git/' --exclude='storage/' \
     --exclude='public/storage' --exclude='bootstrap/cache/' \
     /var/www/html/ce_management_portal_git/ /var/www/html/event.christembassytanzania.org/
   cd /var/www/html/event.christembassytanzania.org && php artisan config:clear
   ```
2. **Inspect the server** (read-only, prints no secrets) and keep the output:
   ```bash
   bash /var/www/html/ce_management_portal_git/scripts/deploy/inspect-server.sh
   ```
   Check: the Git copy is owned by the deploy user (if not: `sudo chown -R josephat: /var/www/html/ce_management_portal_git`), which PHP-FPM service runs, and the PHP-FPM pool group (usually `www-data`).
3. **Install the helper + sudoers rule** (shows everything and asks before changing anything):
   ```bash
   sudo bash /var/www/html/ce_management_portal_git/scripts/deploy/install-deploy-helper.sh --own-live-code
   ```
   `--own-live-code` (recommended, one time): LIVE code → `josephat:www-data`, `.env` → `0640`, so rsync never needs sudo. The storage/ and bootstrap/cache are handled by `fix-permissions`.
   Alternative without changing LIVE ownership: `--with-sync` and `DEPLOY_RSYNC_USE_SUDO=true` in `.env`.
   The installer detects the PHP-FPM service; override with `--php-fpm=php8.2-fpm`, `--web-group=www-data`, `--user=josephat`.
4. **Test** (as `josephat`; none of these may ask for a password):
   ```bash
   sudo -n /usr/local/sbin/ce-deploy-helper check          # prints paths and services
   sudo -n -l /usr/local/sbin/ce-deploy-helper fix-permissions && echo "allowed without password"
   sudo -n true || echo "good: general sudo still needs a password"
   cd /var/www/html/event.christembassytanzania.org && php artisan deploy --dry-run
   ```
5. **First real deployment** (asks for confirmation): `php artisan deploy`

### Removing the sudo configuration

```bash
sudo bash /var/www/html/ce_management_portal_git/scripts/deploy/uninstall-deploy-helper.sh
# or manually:
sudo rm /etc/sudoers.d/ce-deployment /usr/local/sbin/ce-deploy-helper && sudo visudo -c
```

After removal, `php artisan deploy --no-permissions --no-restart` still works (rsync/cache/migrations need no sudo).

### Editing the rule by hand

Always `sudo visudo -f /etc/sudoers.d/ce-deployment` (it validates before saving), never a plain editor.

---

## 8. Logging

- `storage/logs/deployment-YYYY-MM-DD.log` (daily files, older than 30 days deleted automatically — the `deployment` channel).
- Each run logs: start (user, options), commit before/after, rsync counts, permissions, caches, migrations, storage link, service restarts, final status or the failing step + reason.
- Never logged: `.env`, passwords, tokens, database credentials.
- Future cron output should go to a file that you rotate (see §11).

---

## 9. Rollback

Code rollback and database rollback are **separate**.

**Code** (safe, repeatable):

```bash
cd /var/www/html/ce_management_portal_git
git log --oneline -10                      # find the good commit, e.g. abc1234
git checkout abc1234                       # detached HEAD at the good commit
cd /var/www/html/event.christembassytanzania.org
php artisan deploy --dry-run --no-pull     # review what changes
php artisan deploy --no-pull               # deploy that exact commit (add --no-migrate if needed)
```

Back to normal afterwards: fix forward in Git (e.g. `git revert` on your machine, push), then:

```bash
cd /var/www/html/ce_management_portal_git && git checkout main
cd /var/www/html/event.christembassytanzania.org && php artisan deploy
```

(While the Git copy is on a detached commit, a normal `php artisan deploy` checks out `main` again and pulls.)

**Database**: never rolled back automatically. Migrations that already ran stay. If a migration must be undone, write a new forward migration, or — only after a backup and with care — `php artisan migrate:rollback --step=1` manually. Old code must still work with the new schema, or you need a separate migration plan.

---

## 10. Troubleshooting

| Message | Fix |
|---|---|
| `Deployment already in progress.` | Another run holds the lock. Wait. If none is running (e.g. after a server crash) the lock is released automatically — `flock` locks die with the process. |
| `Deployment configuration not loaded` | `php artisan config:clear`, then run again. |
| `The sudo helper is not usable without a password` | Install it (§7.3) or deploy with `--no-permissions --no-restart`. Check `sudo -n -l`. |
| `sudo helper was installed for ... path` | Paths in `config/deployment.php` and the helper differ — re-run the installer. |
| `Not writable by josephat: <path>` | Run the installer with `--own-live-code`, or use `--with-sync` + `DEPLOY_RSYNC_USE_SUDO=true`. |
| `The Git working copy has uncommitted changes` | Someone edited files in the Git copy. Inspect with `git -C /var/www/html/ce_management_portal_git status`; commit them properly or `git checkout -- <file>`. |
| `git pull --ff-only ... failed` | Network/credentials, or the Git copy diverged from GitHub. `GIT_TERMINAL_PROMPT=0` means it never waits for a password — for cron use an SSH deploy key (read-only). |
| `Branch 'main' does not exist` | `git -C /var/www/html/ce_management_portal_git fetch origin && git -C ... checkout -b main origin/main`. |
| Migration failed | The exact SQL error is shown. Code is already synced; fix forward (new commit) or roll back code (§9). Nothing was rolled back automatically. |
| `SERVICE RESTART FAILED` | Code is live. `systemctl status <php-fpm>`, `sudo nginx -t`, `journalctl -u nginx -n 50`. |
| Site shows old config/routes | `php artisan optimize:clear` and run `deploy` again. |

---

## 11. Future cron (not installed)

`--force` is non-interactive and `sudo -n` never prompts, so this works from cron once §7 is done:

```cron
*/5 * * * * cd /var/www/html/event.christembassytanzania.org && php artisan deploy --force >> storage/logs/deploy-cron.log 2>&1
```

Before enabling: make `git pull` work without a prompt (SSH deploy key), and rotate `deploy-cron.log` (e.g. `/etc/logrotate.d/ce-deploy`). Each run that finds no new commit still re-syncs, clears caches and restarts services — for a 5-minute schedule you may want a cheaper check first (e.g. only deploy when `git fetch` finds new commits). The lock prevents overlapping runs.

---

## 12. Security notes

- The deploy user can run exactly four helper actions as root, with fixed arguments and fixed paths. It cannot run arbitrary commands, choose paths, or `chown`/`chmod` anything outside `storage/` and `bootstrap/cache/`.
- `fix-permissions` uses `chown -hRP` / `find -type f|d`: symbolic links are never followed, so a link planted in `storage/` cannot redirect root to other files.
- The helper is `root:root 0755` in `/usr/local/sbin`; the deploy user cannot edit it. The copy in the repository is only a template.
- Anyone who can log in as the deploy user can restart PHP-FPM/Nginx (brief downtime) and change the deployed code — protect that account (SSH keys, no shared passwords).
- `--with-sync` (rsync as root) is broader: files from the Git copy are written by root. Prefer the default (deploy user owns LIVE code).
- `.env` stays out of Git and out of rsync; with `--own-live-code` it becomes `0640` (deploy user + web group only).
- Make sure the live `.env` has `APP_ENV=production` and `APP_DEBUG=false` (debug pages can reveal configuration).
