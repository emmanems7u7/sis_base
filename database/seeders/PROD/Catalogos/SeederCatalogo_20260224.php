<?php

namespace Database\Seeders\PROD\Catalogos;

use Illuminate\Database\Seeder;
use \App\Models\Catalogo;
use Database\Seeders\Traits\RunsOnce;
class SeederCatalogo_20260224 extends Seeder
{
    use RunsOnce;
    protected function handle(): void
    {
        $catalogos = [
            [
                'id' => 137,
                'categoria_id' => 7,
                'catalogo_parent' => '',
                'catalogo_codigo' => 'CAMPF-028',
                'catalogo_descripcion' => 'Campo relación de formulario',
                'catalogo_estado' => '1',
            ],
            [
                'id' => 136,
                'categoria_id' => 7,
                'catalogo_parent' => '',
                'catalogo_codigo' => 'CAMPF-027',
                'catalogo_descripcion' => 'Campo autocompletado',
                'catalogo_estado' => '1',
            ],
        ];

        foreach ($catalogos as $data) {
            Catalogo::firstOrCreate(
                ['catalogo_codigo' => $data['catalogo_codigo']],
                $data
            );
        }
    }
}