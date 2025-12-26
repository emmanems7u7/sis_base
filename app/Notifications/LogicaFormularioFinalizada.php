<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LogicaFormularioFinalizada extends Notification
{
    protected bool $ok;
    protected ?string $mensaje;

    public function __construct(bool $ok, string $mensaje = null)
    {
        $this->ok = $ok;
        $this->mensaje = $mensaje;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'tipo' => $this->ok ? 'success' : 'error',
            'titulo' => $this->ok
                ? 'Proceso finalizado'
                : 'Errores en la lógica',
            'mensaje' => $this->ok
                ? 'La lógica del formulario terminó correctamente.'
                : $this->mensaje,
        ];
    }
}
