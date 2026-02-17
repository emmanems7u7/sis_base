<?php

namespace Database\Seeders\GEN;
use Illuminate\Database\Seeder;
use App\Models\ConfiguracionCredenciales;
use Database\Seeders\Traits\RunsOnce;

class ConfiguracionCredencialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        ConfiguracionCredenciales::create([
            'conf_long_min' => 4,
            'conf_long_max' => 15,
            'conf_req_upper' => 0,
            'conf_req_num' => 0,
            'conf_req_esp' => 0,
            'conf_duracion_min' => 10,
            'conf_duracion_max' => 90,
            'conf_tiempo_bloqueo' => 300,
            'conf_defecto' => 'contraseña_000',
        ]);
    }
}
