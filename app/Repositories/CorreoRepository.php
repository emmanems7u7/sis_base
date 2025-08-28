<?php
namespace App\Repositories;

use App\Interfaces\CorreoInterface;
use \App\Models\ConfCorreo;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;

class CorreoRepository extends BaseRepository implements CorreoInterface
{

    public function __construct()
    {
        parent::__construct();

    }
    public function EditarPlantillaCorreo($request, $email)
    {
        $email->nombre = $request->input('nombre_plantilla');
        $email->asunto = $request->input('asunto_plantilla');
        $email->contenido = $request->input('contenido');
        $email->save();
    }
    public function EditarConfCorreo($correoId, $request)
    {
        $confCorreo = ConfCorreo::updateOrCreate(

            ['id' => $correoId],
            [
                'conf_protocol' => $this->cleanHtml($request['conf_correo_protocol']),
                'conf_smtp_host' => $this->cleanHtml($request['conf_smtp_host']),
                'conf_smtp_port' => $this->cleanHtml($request['conf_smtp_port']),
                'conf_smtp_user' => $this->cleanHtml($request['conf_smtp_user']),
                'conf_smtp_pass' => $this->cleanHtml($request['conf_smtp_pass']),
                'conf_mailtype' => $this->cleanHtml($request['conf_mailtype']),
                'conf_charset' => $this->cleanHtml($request['conf_charset']),
                'conf_in_background' => $request['conf_in_background'],
                'accion_usuario' => Auth::user()->name,
            ]
        );

        return $confCorreo;
    }
}
