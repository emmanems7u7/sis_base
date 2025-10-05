<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Support\Str;
class TipoImagenesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Tipo de imagenes para carga',
            'descripcion' => 'Listado de Tipos de imagenes para carga',
            'estado' => 1
        ]);
        $tiposImagenesC = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
            'tiff',
            'svg'
        ];


        foreach ($tiposImagenesC as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TIC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
                'accion_usuario' => 'seeder_' . Str::random(5),
            ]);
        }
    }
}
