<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class SeederController extends Controller
{
    public function index()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Roles', 'url' => route('roles.index')],
        ];

        $basePath = database_path('seeders/' . env('APP_STAGE'));

        $estructura = $this->leerDirectorio($basePath);

        return view('seeders.index', compact('estructura', 'breadcrumb'));
    }

    private function leerDirectorio(string $path, string $base = '')
    {
        $items = [];

        foreach (File::directories($path) as $dir) {
            $nombre = basename($dir);
            $ruta = ltrim($base . '/' . $nombre, '/');

            $items[] = [
                'tipo' => 'carpeta',
                'nombre' => $nombre,
                'ruta' => $ruta,
                'hijos' => $this->leerDirectorio($dir, $ruta),
            ];
        }

        foreach (File::files($path) as $file) {
            $items[] = [
                'tipo' => 'archivo',
                'nombre' => $file->getFilename(),
                'ruta' => ltrim($base . '/' . $file->getFilename(), '/'),
            ];
        }

        return $items;
    }

    public function verSeeder()
    {
        $ruta = request('ruta');

        $path = database_path('seeders/' . env('APP_STAGE') . '/' . $ruta);

        abort_if(!file_exists($path), 404);

        return response()->json([
            'contenido' => file_get_contents($path)
        ]);
    }

}
