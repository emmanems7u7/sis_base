<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfiguracionCredenciales;
class ConfiguracionCredencialesController extends Controller
{
    function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Configuracion Credenciales', 'url' => route('admin.configuracion.edit')],
        ];
        $configuracion = ConfiguracionCredenciales::first();
        return view('configuracion.configuracion_credenciales', compact('breadcrumb', 'configuracion'));
    }

    public function actualizar(Request $request)
    {
        $request->validate([
            'conf_long_min' => 'required|integer|min:1',
            'conf_long_max' => 'required|integer|min:1|gte:conf_long_min',
            'conf_req_upper' => 'required|boolean',
            'conf_req_num' => 'required|boolean',
            'conf_req_esp' => 'required|boolean',
            'conf_duracion_min' => 'required|integer|min:1',
            'conf_duracion_max' => 'required|integer|min:1|gte:conf_duracion_min',
            'conf_tiempo_bloqueo' => 'required|integer|min:0',
            'conf_defecto' => 'required|string',
        ]);

        $configuracion = ConfiguracionCredenciales::first();
        $configuracion->update([
            'conf_long_min' => $request->conf_long_min,
            'conf_long_max' => $request->conf_long_max,
            'conf_req_upper' => $request->conf_req_upper,
            'conf_req_num' => $request->conf_req_num,
            'conf_req_esp' => $request->conf_req_esp,
            'conf_duracion_min' => $request->conf_duracion_min,
            'conf_duracion_max' => $request->conf_duracion_max,
            'conf_tiempo_bloqueo' => $request->conf_tiempo_bloqueo,
            'conf_defecto' => $request->conf_defecto,

        ]);

        return redirect()->route('configuracion.credenciales.index')->with('status', 'Configuración actualizada correctamente.');
    }
}
