<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class Generado_SeederMenu_20251013 extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'id' => '30',
                'nombre' => 'Logs',
                'orden' => 1,
                'padre_id' => null,
                'seccion_id' => 17,
                'ruta' => 'logs.index',
                'modulo_id' => null,
            ],
            [
                'id' => '20',
                'nombre' => 'Gestión de Inventario',
                'orden' => 2,
                'padre_id' => null,
                'seccion_id' => 11,
                'ruta' => 'modulo.index',
                'modulo_id' => 1,
            ],


            [
                'id' => '13',
                'nombre' => 'Módulos Dinamicos',
                'orden' => 2,
                'padre_id' => null,
                'seccion_id' => 10,
                'ruta' => 'modulos.index',
                'modulo_id' => null,

            ],

        ];

        foreach ($menus as $data) {
            Menu::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}