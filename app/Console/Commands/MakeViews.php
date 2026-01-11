<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeViews extends Command
{
    // Definir la firma con un argumento obligatorio "name"
    protected $signature = 'make:views {name}';

    protected $description = 'Crear carpeta de vistas con index, create, edit y _form';

    public function handle()
    {
        $name = $this->argument('name');
        $basePath = resource_path('views/' . $name);

        // Verificar si ya existe la carpeta
        if (File::exists($basePath)) {
            $this->error("La carpeta de vistas '$name' ya existe.");
            return 1; // Error
        }

        // Crear la carpeta
        File::makeDirectory($basePath, 0755, true);

        // Contenido para index, create y edit
        $contenido = <<<EOT
@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                   
                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">
        <div class="card-body">

        </div>
    </div>

@endsection
EOT;

        // Crear los archivos con contenido
        File::put($basePath . '/index.blade.php', $contenido);
        File::put($basePath . '/create.blade.php', $contenido);
        File::put($basePath . '/edit.blade.php', $contenido);

        // Crear _form.blade.php vacío
        File::put($basePath . '/_form.blade.php', '');

        $this->info("Carpeta de vistas '$name' creada correctamente con los archivos.");

        return 0;
    }
}
