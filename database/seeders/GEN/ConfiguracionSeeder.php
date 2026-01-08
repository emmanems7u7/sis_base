<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('configuracion')->insert([
            'id' => 1,
            'doble_factor_autenticacion' => 0,
            'GROQ_API_KEY' => null,
            'mantenimiento' => 0,
            'firma' => 1,
            'hoja_export' => 'A4',
            'limite_de_sesiones' => 2,
            'created_at' => Carbon::parse('2025-04-08 13:18:32'),
            'updated_at' => Carbon::parse('2025-10-13 06:30:33'),
        ]);
    }
}
