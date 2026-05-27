<?php

// app/Http/Controllers/ArtisanController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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

            $comando = trim($request->comando);

            if (str_starts_with($comando, 'artisan')) {

                $comando = str_replace('artisan ', '', $comando);

                Artisan::call($comando);
                $output = Artisan::output();

            } else if (str_starts_with($comando, 'composer')) {

                $process = new Process(explode(' ', $comando));
                $process->setWorkingDirectory(base_path());
                $process->setTimeout(600); // 10 min

                $process->run();

                $output = $process->getOutput() . $process->getErrorOutput();

            } else {
                return view('admin.artisan-panel', [
                    'error' => 'Solo se permiten comandos artisan o composer.',
                    'breadcrumb' => $breadcrumb,
                    'clave_segura' => $request->clave_segura
                ]);
            }

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
