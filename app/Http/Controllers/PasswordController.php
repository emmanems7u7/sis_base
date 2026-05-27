<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Services\CredencialesService;
use App\Models\ConfiguracionCredenciales;
use Illuminate\Support\Facades\Cache;

class PasswordController extends Controller
{

    public function update(Request $request)
    {
        $config = ConfiguracionCredenciales::first();
        $ultimoCambio = Auth::user()->usuario_fecha_ultimo_password;

        if ($ultimoCambio) {
            $diasTranscurridos = Carbon::parse($ultimoCambio)->diffInDays(Carbon::now());

            if ($diasTranscurridos < $config->conf_duracion_min) {
                throw ValidationException::withMessages([
                    'new_password' => ["Debes esperar al menos {$config->conf_duracion_min} días para volver a cambiar tu contraseña."],
                ]);
            }
        }


        if ($config->conf_duracion_min == 0) {
            return redirect()->back()->with('status', 'El tiempo de vigencia minimo de la contraseña es de' . $config->conf_duracion_min . ' dias.');
        }
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed'],
        ], [
            'current_password.required' => 'Por favor, ingrese su contraseña actual.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'new_password.required' => 'La nueva contraseña es obligatoria.',

            'new_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        // Validar la nueva contraseña con el servicio personalizado
        $validation = CredencialesService::validarPassword($request->new_password);

        if (!$validation['success']) {
            // Lanzar excepción con mensajes de error para mostrar en la vista
            throw ValidationException::withMessages([
                'new_password' => $validation['errors'],
            ]);
        }

        // Actualizar contraseña y fecha
        $user = Auth::user()->update([
            'password' => Hash::make($request->new_password),
            'usuario_fecha_ultimo_password' => Carbon::now(),
        ]);

        Cache::forget('password_status_' . $user->id);

        return redirect()->back()->with('status', 'Contraseña actualizada correctamente.');
    }
    function ActualizarContraseña()
    {
        $config = ConfiguracionCredenciales::first();
        $user = Auth::user();

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Actualizar Contraseña', 'url' => route('user.actualizar.contraseña')],
        ];

        if ($user->usuario_fecha_ultimo_password) {
            $ultimoCambio = Carbon::parse($user->usuario_fecha_ultimo_password);

            $diferenciaDias = (int) $ultimoCambio->diffInDays(Carbon::now());

            if ($diferenciaDias >= $config->conf_duracion_max) {
                $tiempo_cambio_contraseña = "Es tiempo de cambiar tu contraseña.";
            } else {
                $tiempo_cambio_contraseña = "La contraseña está actualizada. Quedan " . ($config->conf_duracion_max - $diferenciaDias) . " días para cambiarla.";
            }
        } else {
            $tiempo_cambio_contraseña = "Nunca se ha cambiado la contraseña. Por favor, actualízala.";
        }
        return view('auth.update_password', compact('tiempo_cambio_contraseña', 'breadcrumb'));
    }
}
