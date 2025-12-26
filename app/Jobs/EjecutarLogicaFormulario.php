<?php

namespace App\Jobs;

use App\Interfaces\FormLogicInterface;
use App\Models\Respuesta;
use App\Models\RespuestasForm;
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

    protected $respuesta;
    protected $filasSeleccionadas;
    protected $evento;

    public function __construct(RespuestasForm $respuesta, array $filasSeleccionadas, string $evento)
    {
        $this->respuesta = $respuesta;
        $this->filasSeleccionadas = $filasSeleccionadas;
        $this->evento = $evento;
    }

    public function handle(FormLogicInterface $formLogic)
    {
        $resultado = $formLogic->ejecutarLogica(
            $this->respuesta,
            $this->filasSeleccionadas,
            $this->evento
        );

        $resultado = array_filter($resultado, fn($msg) => !empty(trim($msg)));

        if (!empty($resultado)) {
            Log::error('Error lógica formulario', [
                'respuesta_id' => $this->respuesta->id,
                'errores' => $resultado,
            ]);
        }
    }

    public function failed(Throwable $exception)
    {
        Log::critical('Job EjecutarLogicaFormulario falló', [
            'respuesta_id' => $this->respuesta->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
