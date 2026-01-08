<?php

namespace Database\Seeders\GEN;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

use App\Models\Catalogo;
use Illuminate\Support\Str;

class FormulariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Estado Formulario',
            'descripcion' => 'Listado de estados del formulario',
            'estado' => 1
        ]);

        $estados = ['borrador', 'publicado', 'archivado'];

        foreach ($estados as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'EFORM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
                'accion_usuario' => 'seeder_' . Str::random(5),
            ]);
        }
    }
}
