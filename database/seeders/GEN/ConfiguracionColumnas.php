<?php

namespace Database\Seeders\GEN;


use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Database\Seeder;

class ConfiguracionColumnas extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Configuracion Columnas',
            'descripcion' => 'Combinaciones de distribución responsive para formularios',
            'estado' => 1
        ]);
        $campos = [
            'movil-1|desktop-1',
            'movil-1|desktop-2',
            'movil-1|desktop-3',
            'movil-1|desktop-4',

            'movil-2|desktop-2',
            'movil-2|desktop-3',
            'movil-2|desktop-4',

            'movil-3|desktop-3',
            'movil-3|desktop-4',

            'movil-4|desktop-4',

            'movil-1|desktop-12',
        ];

        foreach ($campos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'CFGCOL-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
