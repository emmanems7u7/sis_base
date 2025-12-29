<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LOCAL_SeederPermisos_20251226141006 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 100, 'name' => 'Acciones Ejecutadas', 'tipo' => 'menu', 'id_relacion' => 33, 'guard_name' => 'web' ],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}