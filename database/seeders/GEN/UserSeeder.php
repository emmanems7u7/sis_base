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
                'name' => 'usuario' . $i, // Nombre fijo por ahora
                'email' => 'usuario' . $i . '@correo.com', // Email único
                'password' => Hash::make('password' . $i), // Contraseña con hash

                'usuario_fecha_ultimo_acceso' => Carbon::now()->subDays(rand(1, 30)), // Fecha aleatoria de último acceso
                'usuario_fecha_ultimo_password' => Carbon::now()->subDays(rand(10, 60)), // Fecha aleatoria de último cambio de contraseña
                'usuario_nombres' => fake()->firstName, // Nombre aleatorio
                'usuario_app' => fake()->lastName, // Apellido paterno aleatorio
                'usuario_apm' => fake()->lastName, // Apellido materno aleatorio
                'usuario_telefono' => fake()->phoneNumber, // Teléfono aleatorio
                'usuario_direccion' => fake()->address, // Dirección aleatoria
                'accion_fecha' => Carbon::now(), // Fecha actual
                'accion_usuario' => 'system', // Usuario de acción
                'usuario_activo' => 1, // Usuario activo
            ]);
        }
    }


}
