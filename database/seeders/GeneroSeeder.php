<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Support\Str;

class GeneroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Generos',
            'descripcion' => 'Listado de Generos',
            'estado' => 1
        ]);

        // Familiares desde los cuales el paciente puede heredar enfermedades
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
                'accion_usuario' => 'seeder_' . Str::random(5),
            ]);
        }
    }
}
