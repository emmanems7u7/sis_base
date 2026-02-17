<?php

namespace Database\Seeders\GEN;

use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\RunsOnce;

class WidgetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        $categoriaWidgets = Categoria::create([
            'nombre' => 'Tipos de Widget',
            'descripcion' => 'Tipos de widgets que se pueden agregar al dashboard o home',
            'estado' => 1
        ]);

        $tiposWidget = [
            'boton',
            'estadistica',
            'calendario',
            'tabla',
            'lista',
            'formulario',
            'grafico_linea',
            'grafico_barra',
            'grafico_pastel',
            'contador',
            'reporte',
            'notificacion',
            'link',
            'avatar',
            'texto',
        ];

        foreach ($tiposWidget as $index => $tipo) {
            Catalogo::create([
                'categoria_id' => $categoriaWidgets->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'WID-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $tipo,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
