<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Support\Str;
class EstadosActivoInactivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categoria = Categoria::create([
            'nombre' => 'Estados Activo/Inactivo',
            'descripcion' => 'Listado de Estados Activo/Inactivo',
            'estado' => 1
        ]);
        $campos = [
            'Activo',
            'Inactivo',

        ];

        foreach ($campos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'EAI-' . str_pad($index + 12, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
