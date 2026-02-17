<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Traits\RunsOnce;

class ConfCorreoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    use RunsOnce;
    protected function handle()
    {
        DB::table('conf_correos')->insert([
            'mailer' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'usuario@example.com',
            'password' => bcrypt('secret_password'),
            'encryption' => 'tls',
            'from_address' => 'no-reply@example.com',
            'from_name' => 'Nombre de la App',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
