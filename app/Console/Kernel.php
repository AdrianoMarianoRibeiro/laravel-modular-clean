<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Exemplo de tarefas agendadas
        
        // Limpar logs antigos diariamente à meia-noite
        $schedule->command('log:clear')->daily();
        
        // Limpar arquivos temporários a cada hora
        // $schedule->command('app:cleanup-temp')->hourly();
        
        // Processar relatórios semanalmente
        // $schedule->command('reports:generate')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
