<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class Generado_SeederPermisos_20251016 extends Seeder
{
    public function run()
    {
        $permisos = [
            ['id' => 97, 'name' => 'Logica de negocio', 'tipo' => 'menu', 'id_relacion' => 31, 'guard_name' => 'web' ],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso['name'], 'tipo' => $permiso['tipo']],
                $permiso
            );
        }
    }
}