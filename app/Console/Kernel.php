<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * @var array
     */
    protected $commands = [
        // Commands are auto-discovered via composer.json
    ];

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
