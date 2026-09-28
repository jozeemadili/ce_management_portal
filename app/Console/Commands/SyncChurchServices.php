<?php

namespace App\Console\Commands;

use App\Services\ChurchServices;
use Illuminate\Console\Command;

/**
 * Opens church services when their check-in window starts and closes them
 * when they end (marking members who never checked in as absent).
 * Scheduled every minute in App\Console\Kernel.
 */
class SyncChurchServices extends Command
{
    protected $signature = 'services:sync';

    protected $description = 'Open church services at their start time and close them (mark absentees) when they end';

    public function handle(ChurchServices $services)
    {
        $result = $services->sync();

        if ($result['opened'] || $result['closed']) {
            $this->info("Opened {$result['opened']}, closed {$result['closed']} service(s).");
        }

        return self::SUCCESS;
    }
}
