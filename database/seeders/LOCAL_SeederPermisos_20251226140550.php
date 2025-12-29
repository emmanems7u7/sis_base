<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LOCAL_SeederPermisos_20251226140550 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 98, 'name' => 'Auditoria', 'tipo' => 'seccion', 'id_relacion' => 18, 'guard_name' => 'web' ],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}