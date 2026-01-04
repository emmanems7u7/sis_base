<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class LOCAL_SeederMenu_20260103 extends Seeder
{
    public function run(): void
    {
        $menus = [
                [
                    'id' => '35',
                    'nombre' => 'Estudiantes',
                    'orden' => 2,
                    'padre_id' => null,
                    'seccion_id' => 20,
                    'ruta' => 'modulo.index',
                    'modulo_id' => '4',
                ],                [
                    'id' => '34',
                    'nombre' => 'Asistencia',
                    'orden' => 1,
                    'padre_id' => null,
                    'seccion_id' => 20,
                    'ruta' => 'modulo.index',
                    'modulo_id' => '3',
                ],];

        foreach ($menus as $data) {
            Menu::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}