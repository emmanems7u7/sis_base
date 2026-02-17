<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\Traits\RunsOnce;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {

        $permissionManageSecciones = Permission::create(['name' => 'manage secciones']);
        $permissionManageMenus = Permission::create(['name' => 'manage menus']);


        $roleAdmin = Role::create(['name' => 'admin']);
        $roleEditor = Role::create(['name' => 'editor']);


        $roleAdmin->givePermissionTo($permissionManageSecciones);
        $roleAdmin->givePermissionTo($permissionManageMenus);

        $roleEditor->givePermissionTo($permissionManageSecciones);


        $adminUser = \App\Models\User::find(1);
        $adminUser->assignRole('admin');
    }
}
