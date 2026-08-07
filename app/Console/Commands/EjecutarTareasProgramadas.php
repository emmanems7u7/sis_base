<?php

namespace App\Console\Commands;

use App\Interfaces\FormLogicInterface;
use Illuminate\Console\Command;

class EjecutarTareasProgramadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tareas:ejecutar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta las tareas programadas';

    /**
     * Execute the console command.
     */
    public function handle(FormLogicInterface $repo)
    {
        $repo->ejecutarTareasProgramadas();
    }
}
