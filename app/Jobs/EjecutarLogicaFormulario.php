<?php

namespace App\Jobs;

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

    protected $respuesta;
    protected $filasSeleccionadas;
    protected $evento;
    protected $usuario;

    public function __construct(RespuestasForm $respuesta, array $filasSeleccionadas, string $evento, $usuario)
    {
        $this->respuesta = $respuesta;
        $this->filasSeleccionadas = $filasSeleccionadas;
        $this->evento = $evento;
        $this->usuario = $usuario;
    }
    public function handle(FormLogicInterface $formLogic)
    {
        $resultado = $formLogic->ejecutarLogica(
            $this->respuesta,
            $this->filasSeleccionadas,
            $this->evento,
            $this->usuario
        );

        // 🔔 Notificación global al usuario
        $user = User::find($this->usuario);

        if ($user && !empty($resultado['acciones_ejecutadas'])) {
            foreach ($resultado['acciones_ejecutadas'] as $accion) {
                // Crear un array con la estructura que espera tu notificación
                $detalle = [
                    'accion_id' => $accion['accion_id'] ?? null,
                    'tipo_accion' => $accion['tipo_accion'] ?? null,
                    'mensaje' => $accion['mensaje'] ?? '',
                    'detalle' => $accion['detalle'] ?? [],
                    'errores' => $accion['errores'] ?? [],
                    'ok' => $accion['ok'] ?? false,
                ];

                $user->notify(new LogicaFormularioFinalizada($detalle));
            }
            // 🧾 Auditoría general del evento
            AuditoriaAccion::create([
                'action_id' => null,
                'tipo_accion' => 'LOGICA_FORMULARIO',
                'usuario_id' => $this->usuario,
                'estado' => $accion['ok'] ? 'success' : 'error',
                'mensaje' => $accion['mensaje'],
                'detalle' => $accion,
                'errores' => $accion['errores'],
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
