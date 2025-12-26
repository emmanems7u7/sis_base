<?php

// app/Http/Controllers/ArtisanController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;
class ArtisanController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Panel de Artisan', 'url' => route('artisan.admin')],
        ];

        $request->validate([
            'clave_segura' => 'required|string',
        ]);



        if ($request->clave_segura !== env('ARTISAN_PANEL_PASSWORD')) {
            return back()->with('error', 'Contraseña incorrecta');
        }

        $clave_segura = $request->clave_segura;
        return view('admin.artisan-panel', compact('clave_segura', 'breadcrumb'));
    }

    public function run(Request $request)
    {
        $request->validate([
            'comando' => 'required|string',
            'clave_segura' => 'required|string',
        ]);

        if ($request->clave_segura !== env('ARTISAN_PANEL_PASSWORD')) {
            return redirect()->route('artisan.admin')->with('error', 'Contraseña inválida.');
        }
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Panel de Artisan', 'url' => route('artisan.admin')],
        ];
        try {
            Artisan::call($request->comando);
            $output = Artisan::output();

            return view('admin.artisan-panel', [
                'output' => $output,
                'breadcrumb' => $breadcrumb,
                'clave_segura' => $request->clave_segura
            ]);
        } catch (CommandNotFoundException $e) {
            return view('admin.artisan-panel', [
                'error' => 'Comando no reconocido.',
                'breadcrumb' => $breadcrumb,
                'clave_segura' => $request->clave_segura
            ]);
        } catch (\Exception $e) {
            return view('admin.artisan-panel', [
                'error' => 'Error al ejecutar: ' . $e->getMessage(),
                'breadcrumb' => $breadcrumb,
                'clave_segura' => $request->clave_segura
            ]);
        }
    }

    public function verificacion()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Panel de Artisan', 'url' => route('artisan.admin')],
        ];
        return view('admin.artisan-verificacion', compact('breadcrumb'));
    }
    public function verificar(Request $request)
    {
        $request->validate([
            'clave_segura' => 'required|string',
        ]);

        if ($request->clave_segura === env('ARTISAN_PANEL_PASSWORD')) {
            session(['artisan_access_granted' => true]);
            return redirect()->route('artisan.admin');
        }

        return back()->with('error', 'Contraseña incorrecta');
    }
}
