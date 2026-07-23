<?php

namespace App\Jobs;

use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormLogicInterface;
use App\Models\AuditoriaAccion;
use App\Models\Respuesta;
use App\Models\RespuestasForm;
use App\Models\User;
use App\Notifications\LogicaFormularioFinalizada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class EjecutarLogicaFormulario implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;

    protected $respuestas;
    protected $evento;
    protected $usuario;
    protected $url;
    protected $reglas;
    protected $esCascada;
    public function __construct($reglas, array $respuestas, string $evento, $usuario, $url, $esCascada)
    {
        $this->reglas = $reglas;
        $this->respuestas = $respuestas;
        $this->evento = $evento;
        $this->usuario = $usuario;
        $this->url = $url;
        $this->esCascada = $esCascada
    }
    public function handle(FormLogicInterface $formLogic)
    {
        $formLogic->EjecutarReglaLogica(
            $this->reglas,
            $this->respuestas,
            $this->evento,
            auth()->id(),
            env('APP_URL'),
            $this->esCascada
        );

    }

    public function failed(Throwable $exception)
    {
        Log::critical('Job EjecutarLogicaFormulario falló', [
            'respuesta_id' => $this->respuesta->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
