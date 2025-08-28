<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class Generado_SeederMenu_20250828 extends Seeder
{
    public function run(): void
    {
        $menus = [            [
                'id' => '12',
                'nombre' => 'Formularios',
                'orden' => 1,
                'padre_id' => null,
                'seccion_id' => 11,
                'ruta' => 'formularios.index',
                'accion_usuario' => '',
            ],];

        foreach ($menus as $data) {
            Menu::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}