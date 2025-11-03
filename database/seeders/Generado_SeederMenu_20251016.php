<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class Generado_SeederMenu_20251016 extends Seeder
{
    public function run(): void
    {
        $menus = [            [
                'id' => '31',
                'nombre' => 'Logica de negocio',
                'orden' => 3,
                'padre_id' => null,
                'seccion_id' => 11,
                'ruta' => 'form-logic.index',
                'modulo_id' => '',
            ],];

        foreach ($menus as $data) {
            Menu::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}