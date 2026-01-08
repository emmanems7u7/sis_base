<?php

namespace Database\Seeders\QA;

use Illuminate\Database\Seeder;

use Database\Seeders\QA\Catalogos\CatalogosSeeder;
use Database\Seeders\QA\Categorias\CategoriasSeeder;
use Database\Seeders\QA\Secciones\SeccionesSeeder;
use Database\Seeders\QA\Menus\MenusSeeder;
use Database\Seeders\QA\Permisos\PermisosSeeder;


class QASeeder extends Seeder
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