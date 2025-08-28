<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class Generado_SeederPermisos_20250828 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 72, 'name' => 'Formularios', 'tipo' => 'menu', 'id_relacion' => 12, 'guard_name' => 'web' ],
            ['id' => 71, 'name' => 'Gestión de Formularios', 'tipo' => 'seccion', 'id_relacion' => 11, 'guard_name' => 'web' ],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}