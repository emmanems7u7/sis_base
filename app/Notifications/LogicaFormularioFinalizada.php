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
    public $ruta;

    /**
     * Crear una nueva instancia de notificación.
     *
     * @param array $detalleEjecucion
     * @param  $ruta
     * 
     */
    public function __construct(array $detalleEjecucion, $ruta)
    {

        $this->detalleEjecucion = $detalleEjecucion;
        $this->ruta = $ruta;

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
            'url' => $this->ruta
            //'url' => route('formulario.auditoria.detalle', ['accion_id' => $this->detalleEjecucion['accion_id']])
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
            'url' => $this->ruta
            // 'url' => route('formulario.auditoria.detalle', ['accion_id' => $this->detalleEjecucion['accion_id']], false)

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
            ->action('Ver detalles', $this->ruta)
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
