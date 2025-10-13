<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seccion;

class Generado_SeederSeccion_20251013 extends Seeder
{
    public function run(): void
    {
        $secciones = [
            [
                'id' => 17,
                'titulo' => 'Administración de Logs',
                'icono' => 'fas fa-file-alt',
                'posicion' => 6,
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