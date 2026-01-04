<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seccion;

class LOCAL_SeederSeccion_20260103 extends Seeder
{
    public function run(): void
    {
        $secciones = [
                [
                    'id' => 20,
                    'titulo' => 'LOGICA DE NEGOCIO DE SISTEMA',
                    'icono' => 'fas fa-server',
                    'posicion' => 9,
                ],                [
                    'id' => 19,
                    'titulo' => 'LOGICA DE NEGOCIO',
                    'icono' => 'fas fa-chart-line',
                    'posicion' => 8,
                ],];

        foreach ($secciones as $data) {
            Seccion::firstOrCreate(
                ['titulo' => $data['titulo']],
                $data
            );
        }
    }
}