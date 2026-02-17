<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use App\Models\Catalogo;
use App\Models\Categoria;
use Database\Seeders\Traits\RunsOnce;

class TipoVideosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    use RunsOnce;
    protected function handle()
    {
        $categoria = Categoria::create([
            'nombre' => 'Tipo de videos para carga',
            'descripcion' => 'Listado de Tipos de videos para carga',
            'estado' => 1
        ]);

        $tiposVideosC = [
            'mp4',
            'mov',
            'avi',
            'wmv',
            'flv',
            'mkv',
            'webm',
            'mpeg'
        ];


        foreach ($tiposVideosC as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TVC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
