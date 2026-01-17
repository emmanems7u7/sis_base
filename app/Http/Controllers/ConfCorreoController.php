<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\ConfCorreo;
use Illuminate\Support\Facades\Auth;
use App\Mail\CorreoPrueba;
use Illuminate\Support\Facades\Mail;
use App\Models\PlantillaCorreo;
use App\Mail\CorreoDesdePlantilla;
use App\Interfaces\CorreoInterface;
use App\Services\DynamicMailer;
class ConfCorreoController extends Controller
{
    protected $correoRepository;
    public function __construct(CorreoInterface $CorreoInterface)
    {

        $this->correoRepository = $CorreoInterface;
    }

    function index()
    {
        $config = ConfCorreo::first();


        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Configuracion', 'url' => route('configuracion.correo.index')],
        ];
        return view('configuracion.configuracion_correo', compact('breadcrumb', 'config'));
    }


    public function update(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|email',
            'password' => 'required|string',
            'encryption' => 'nullable|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $config = ConfCorreo::first();

        if ($config) {
            $config->update($request->all());
        } else {
            ConfCorreo::create($request->all());
        }

        return redirect()->back()
            ->with('status', 'Configuración actualizada correctamente.');
    }

    public function enviarPrueba()
    {
        $conf = ConfCorreo::first();


        if (!$conf) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        $mailer = new DynamicMailer($conf);

        $mailer->send($conf->from_address, new CorreoPrueba('Mensaje de prueba'));

        return response()->json(['mensaje' => 'Correo enviado correctamente.']);
    }





}
