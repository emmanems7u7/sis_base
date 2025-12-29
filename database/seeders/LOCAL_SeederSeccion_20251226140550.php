<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seccion;

class LOCAL_SeederSeccion_20251226140550 extends Seeder
{
    public function run(): void
    {
        $secciones = [                [
                    'id' => 18,
                    'titulo' => 'Auditoria',
                    'icono' => 'fas fa-book-reader',
                    'posicion' => 7,
                ],];

        foreach ($secciones as $data) {
            Seccion::firstOrCreate(
                ['titulo' => $data['titulo']],
                $data
            );
        }
    }
}