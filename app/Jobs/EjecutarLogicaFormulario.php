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
    public function __construct(array $respuestas, string $evento, $usuario, $url)
    {
        $this->respuestas = $respuestas;
        $this->evento = $evento;
        $this->usuario = $usuario;
        $this->url = $url;
    }
    public function handle(FormLogicInterface $formLogic, CatalogoInterface $catalogoInterface)
    {
        $user = User::find($this->usuario);

        // 🔹 Cargar todas las respuestas primero
        $respuestasModelos = collect();

        foreach ($this->respuestas as $item) {

            $respuesta = RespuestasForm::find($item['respuesta_id']);

            if ($respuesta) {
                $respuesta->filasSeleccionadas = $item['filas'];
                $respuestasModelos->push($respuesta);
            }
        }

        // 🔥 Llamar UNA SOLA VEZ
        $resultado = $formLogic->ejecutarLogica(
            $respuestasModelos,
            $this->evento,
            $this->usuario
        );



        if ($user && !empty($resultado['acciones_ejecutadas'])) {

            foreach ($resultado['acciones_ejecutadas'] as $accion) {

                $tipo_accion = $catalogoInterface
                    ->getNombreCatalogo($accion['tipo_accion']);

                $detalle = [
                    'accion_id' => $accion['accion_id'] ?? null,
                    'tipo_accion' => $tipo_accion ?? null,
                    'mensaje' => $accion['mensaje'] ?? '',
                    'detalle' => $accion['detalle'] ?? [],
                    'errores' => $accion['errores'] ?? [],
                    'ok' => $accion['ok'] ?? false,
                ];

                $auditoria = AuditoriaAccion::create([
                    'action_id' => $accion['accion_id'],
                    'tipo_accion' => $tipo_accion,
                    'usuario_id' => $this->usuario,
                    'estado' => $accion['ok'] ? 'success' : 'error',
                    'mensaje' => $accion['mensaje'],
                    'detalle' => $accion,
                    'errores' => $accion['errores'],
                ]);

                $ruta = $this->url . '/formulario/logica/detalle/' . $auditoria->id;

                $user->notify(
                    new LogicaFormularioFinalizada($detalle, $ruta)
                );
            }
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
