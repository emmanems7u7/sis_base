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
                'modulo' => '',
            ],[
                'id' => '20',
                'nombre' => 'Gestión de Inventario',
                'orden' => 2,
                'padre_id' => null,
                'seccion_id' => 11,
                'ruta' => 'modulo.index',
                'modulo' => '',
            ],


            [
                'id' => '13',
                'nombre' => 'Módulos Dinamicos',
                'orden' => 2,
                'padre_id' => null,
                'seccion_id' => 10,
                'ruta' => 'modulos.index',
                'accion_usuario' => '',
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