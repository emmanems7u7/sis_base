<?php

namespace Database\Seeders\GEN;


use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'usuario' . $i,
                'email' => 'usuario' . $i . '@correo.com',
                'password' => Hash::make('password' . $i),
                'usuario_fecha_ultimo_acceso' => Carbon::now()->subDays(rand(1, 30)),
                'usuario_fecha_ultimo_password' => Carbon::now()->subDays(rand(10, 60)),
                'usuario_nombres' => fake()->firstName,
                'usuario_app' => fake()->lastName,
                'usuario_apm' => fake()->lastName,
                'usuario_telefono' => fake()->phoneNumber,
                'usuario_direccion' => fake()->address,
                'usuario_activo' => 1,
            ]);
        }
    }


}
