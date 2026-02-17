<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Support\Str;
use Database\Seeders\Traits\RunsOnce;

class CamposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
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
            'Imagen',
            'Video',
            'Enlace',
            'Fecha',
            'Hora',
            'Archivo',
            'Color',
            'Email',
            'Password',
        ];

        foreach ($campos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'CAMPF-' . str_pad($index + 12, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
