<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;

class LogicaFormularioFinalizada extends Notification
{
    use Queueable;

    public $detalleEjecucion;

    /**
     * Crear una nueva instancia de notificación.
     *
     * @param array $detalleEjecucion
     */
    public function __construct(array $detalleEjecucion)
    {
        // $detalleEjecucion puede tener estructura:
        // [
        //   'accion_id' => 123,
        //   'tipo_accion' => 'TAC-001',
        //   'detalle' => 'Se actualizaron 3 campos: ...',
        //   'errores' => '',
        //   'mensaje' => 'Acción ejecutada correctamente',
        //   'ok' => true
        // ]
        $this->detalleEjecucion = $detalleEjecucion;
    }

    /**
     * Obtener los canales de notificación.
     */
    public function via($notifiable)
    {
        // Puedes agregar 'mail', 'database', 'broadcast' según tu necesidad
        return ['database', 'broadcast'];
    }

    /**
     * Representación para base de datos.
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Ejecución de reglas de formulario',
            'message' => $this->generarMensajeHumano(),
            'detalle' => $this->detalleEjecucion,
            'url' => route('formulario.logica.detalle', ['accion_id' => $this->detalleEjecucion['accion_id']])
        ];
    }

    /**
     * Representación para broadcast (para notificaciones en tiempo real).
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Ejecución de reglas de formulario',
            'message' => $this->generarMensajeHumano(),
            'detalle' => $this->detalleEjecucion,
            //  'url' => route('formulario.logica.detalle', ['accion_id' => $this->detalleEjecucion['accion_id']])
            'url' => url('/formulario/logica/detalle/' . $this->detalleEjecucion['accion_id'])
        ]);
    }

    /**
     * Opcional: mensaje para email
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Ejecución de reglas de formulario finalizada')
            ->greeting('Hola ' . $notifiable->name)
            ->line($this->generarMensajeHumano())
            ->action('Ver detalles', route('formulario.logica.detalle', ['accion_id' => $this->detalleEjecucion['accion_id']]))
            ->line('Gracias por usar nuestro sistema.');
    }

    /**
     * Generar un mensaje más humanizado para mostrar al usuario.
     */
    protected function generarMensajeHumano(): string
    {
        $tipoAccion = $this->detalleEjecucion['tipo_accion'] ?? 'DESCONOCIDO';
        $accionId = $this->detalleEjecucion['accion_id'] ?? 'N/A';

        $msg = "La acción '{$tipoAccion}' (ID: {$accionId}) ";

        if (!empty($this->detalleEjecucion['errores'])) {

            $msg .= "finalizó con errores";
        } else {

            $msg .= "se ejecutó correctamente";
        }

        return $msg;
    }
}
