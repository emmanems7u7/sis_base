<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Database\Seeders\Traits\RunsOnce;

class GeneroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        $categoria = Categoria::create([
            'nombre' => 'Generos',
            'descripcion' => 'Listado de Generos',
            'estado' => 1
        ]);


        $generos = [
            'Hombre',
            'Mujer',
            'Otro'

        ];

        foreach ($generos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'GEN-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
