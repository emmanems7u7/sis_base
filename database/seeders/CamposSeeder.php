<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Support\Str;
class CamposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //text


        $categoria = Categoria::create([
            'nombre' => 'Campos Formulario',
            'descripcion' => 'Listado de campos de formulario',
            'estado' => 1
        ]);

        $campos = [
            'Text',
            'Number',
            'Textarea',
            'Checkbox',
            'Radio',
            'Selector',
        ];

        foreach ($campos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'CAMPF-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
                'accion_usuario' => 'seeder_' . Str::random(5),
            ]);
        }
    }
}
