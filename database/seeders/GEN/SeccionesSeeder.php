<?php

namespace Database\Seeders\GEN;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Database\Seeders\Traits\RunsOnce;

class SeccionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        DB::table('secciones')->insert([
            [
                'id' => 5,
                'titulo' => 'Administración de Usuarios',
                'icono' => 'fas fa-users',
                'posicion' => 1,
                'created_at' => Carbon::parse('2025-04-09 19:59:32'),
                'updated_at' => Carbon::parse('2025-04-09 19:59:32'),
            ],
            [
                'id' => 6,
                'titulo' => 'Configuración',
                'icono' => 'fas fa-cog',
                'posicion' => 2,
                'created_at' => Carbon::parse('2025-04-11 18:43:25'),
                'updated_at' => Carbon::parse('2025-04-11 18:43:25'),
            ],
            [
                'id' => 7,
                'titulo' => 'Roles y Permisos',
                'icono' => 'fas fa-user-lock',
                'posicion' => 3,
                'created_at' => Carbon::parse('2025-04-14 18:42:46'),
                'updated_at' => Carbon::parse('2025-04-14 18:42:46'),
            ],
            [
                'id' => 10,
                'titulo' => 'Administración y Parametrización',
                'icono' => 'fas fa-cogs',
                'posicion' => 4,
                'created_at' => Carbon::parse('2025-05-02 18:38:17'),
                'updated_at' => Carbon::parse('2025-05-02 18:38:17'),
            ],
        ]);
    }
}
