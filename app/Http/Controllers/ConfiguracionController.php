<?php

namespace App\Http\Controllers;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use App\Models\Token;


class ConfiguracionController extends Controller
{
    public function edit()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Configuracion', 'url' => route('admin.configuracion.edit')],
        ];
        $config = Configuracion::first();
        $tokens = Token::where('tipo', 'groq')->get();
        return view('configuracion.configuracion_general', compact('tokens', 'config', 'breadcrumb'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'doble_factor_autenticacion' => 'nullable|boolean',
            'limite_de_sesiones' => 'nullable|integer|min:1',
            'tokens.*.token' => 'required|string',
            'tokens.*.estado' => 'required',
            'firma' => 'nullable|boolean',
            'hoja_export' => 'nullable|string|max:255',
        ]);

        $config = Configuracion::first();

        $config->update([
            'doble_factor_autenticacion' => $request->has('doble_factor_autenticacion'),
            'limite_de_sesiones' => $request->input('limite_de_sesiones'),
            'mantenimiento' => $request->has('mantenimiento'),
            'firma' => $request->boolean('firma'),
            'hoja_export' => $request->input('hoja_export'),
        ]);


        foreach ($request->tokens as $data) {
            Token::updateOrCreate(
                ['token' => $data['token']],
                ['estado' => 1, 'tipo' => 'groq']
            );
        }


        return redirect()->back()->with('status', 'Configuración actualizada.');
    }
}
