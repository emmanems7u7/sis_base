<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //SEEDERS GENERALES
        //$this->call(\Database\Seeders\GEN\GENSeeder::class);


        //SEEDERS POR ENTORNO
        switch (strtoupper(env('APP_STAGE'))) {
            case 'DEV':
                $this->call(\Database\Seeders\DEV\DevSeeder::class);
                break;

            case 'QA':
                $this->call(\Database\Seeders\QA\QaSeeder::class);
                break;

            case 'PRODUCCION':
                $this->call(\Database\Seeders\PROD\ProdSeeder::class);
                break;

            default:
                throw new \Exception('Entorno no soportado: ' . env('APP_ENV'));
        }


    }
}
