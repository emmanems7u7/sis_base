<?php

// app/Http/Controllers/LogController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    protected $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    // Listar archivos de log
    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Logs', 'url' => route('logs.index')],
        ];

        $logs = collect(File::files(storage_path('logs')))
            ->map(fn($f) => $f->getFilename())
            ->sortDesc();

        return view('logs.index', compact('logs', 'breadcrumb'));
    }

    public function show($filename)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Logs', 'url' => route('logs.index')],
        ];
        $logs = collect(File::files(storage_path('logs')))
            ->map(fn($f) => $f->getFilename())
            ->sortDesc();

        $ruta = storage_path('logs/' . $filename);
        $contenido = File::exists($ruta) ? File::get($ruta) : 'Archivo no encontrado';

        return view('logs.index', compact('breadcrumb', 'logs', 'filename', 'contenido'));
    }





}
