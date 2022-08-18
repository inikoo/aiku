<?php

namespace App\Console;

use App\Actions\Organisations\UserLinkCode\CleanUserLinkCode;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(CleanUserLinkCode::class)->hourly();

    }


    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
