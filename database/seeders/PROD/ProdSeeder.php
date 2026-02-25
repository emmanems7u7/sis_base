<?php

namespace Database\Seeders\PROD;

use Illuminate\Database\Seeder;

use Database\Seeders\PROD\Catalogos\CatalogosSeeder;
use Database\Seeders\PROD\Categorias\CategoriasSeeder;
use Database\Seeders\PROD\Secciones\SeccionesSeeder;
use Database\Seeders\PROD\Menus\MenusSeeder;
use Database\Seeders\PROD\Permisos\PermisosSeeder;


class ProdSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogosSeeder::class,
            CategoriasSeeder::class,
            SeccionesSeeder::class,
            MenusSeeder::class,
            PermisosSeeder::class,
        ]);
    }
}