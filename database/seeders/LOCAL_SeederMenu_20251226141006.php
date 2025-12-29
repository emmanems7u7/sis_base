<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class LOCAL_SeederMenu_20251226141006 extends Seeder
{
    public function run(): void
    {
        $menus = [                [
                    'id' => '33',
                    'nombre' => 'Acciones Ejecutadas',
                    'orden' => 1,
                    'padre_id' => null,
                    'seccion_id' => 18,
                    'ruta' => 'formulario.auditoria.index',
                    'modulo_id' => 'null',
                ],];

        foreach ($menus as $data) {
            Menu::firstOrCreate(
                ['nombre' => $data['nombre']],
                $data
            );
        }
    }
}