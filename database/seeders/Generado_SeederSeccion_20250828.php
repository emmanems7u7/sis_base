<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seccion;

class Generado_SeederSeccion_20250828 extends Seeder
{
    public function run(): void
    {
        $secciones = [            [
                'id' => 11,
                'titulo' => 'Gestión de Formularios',
                'icono' => 'fas fa-cogs',
                'posicion' => 5,
                'accion_usuario' => '',
            ],];

        foreach ($secciones as $data) {
            Seccion::firstOrCreate(
                ['titulo' => $data['titulo']],
                $data
            );
        }
    }
}