<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LOCAL_SeederPermisos_20260103 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 104, 'name' => 'Estudiantes', 'tipo' => 'menu', 'id_relacion' => 35, 'guard_name' => 'web' ],
            ['id' => 103, 'name' => 'Asistencia', 'tipo' => 'menu', 'id_relacion' => 34, 'guard_name' => 'web' ],
            ['id' => 102, 'name' => 'LOGICA DE NEGOCIO DE SISTEMA', 'tipo' => 'seccion', 'id_relacion' => 20, 'guard_name' => 'web' ],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}