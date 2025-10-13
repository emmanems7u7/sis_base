<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class Generado_SeederPermisos_20251013 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 96, 'name' => 'Logs', 'tipo' => 'menu', 'id_relacion' => 30, 'guard_name' => 'web'],
            ['id' => 95, 'name' => 'Administración de Logs', 'tipo' => 'seccion', 'id_relacion' => 17, 'guard_name' => 'web'],
            ['id' => 80, 'name' => 'Gestión de Inventario', 'tipo' => 'menu', 'id_relacion' => 20, 'guard_name' => 'web'],
            ['id' => 73, 'name' => 'Módulos Dinamicos', 'tipo' => 'menu', 'id_relacion' => 13, 'guard_name' => 'web'],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}