<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
class CategoriaSeeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias')->insert([
            [
                'id' => 1,
                'nombre' => 'Ciudades',
                'descripcion' => 'Listado de ciudades',
                'estado' => 1,
                'created_at' => '2025-06-25 01:29:09',
                'updated_at' => '2025-06-25 01:29:09',
            ],
            [
                'id' => 2,
                'nombre' => 'Paises',
                'descripcion' => 'Lista de paises disponibles',
                'estado' => 1,
                'created_at' => '2025-06-25 02:43:10',
                'updated_at' => '2025-06-25 02:43:10',
            ],

            [
                'id' => 3,
                'nombre' => 'Departamentos',
                'descripcion' => 'Categoria de departamentos',
                'estado' => 1,
                'created_at' => '2025-07-02 23:03:43',
                'updated_at' => '2025-07-02 23:03:43',
            ],
        ]);
    }
}
