<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Sync automatico ogni giorno alle 9:15 (i CSV MIMIT escono tra le 8 e le 11)
        $schedule->command('fuel:sync --province=RO')
            ->dailyAt('09:15')
            ->withoutOverlapping()
            ->runInBackground()
            ->onFailure(function () {
                // Opzionale: notifica Slack/email in caso di fallimento
                \Illuminate\Support\Facades\Log::error('[Scheduler] fuel:sync fallito');
            });
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
