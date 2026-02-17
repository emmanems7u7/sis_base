<?php

namespace Database\Seeders\GEN;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\Traits\RunsOnce;


class CatalogoSeeder extends Seeder
{

    use RunsOnce;
    protected function handle()
    {


        DB::table('catalogos')->insert([
            // País
            [
                'catalogo_parent' => null,
                'catalogo_codigo' => 'P-001',
                'catalogo_descripcion' => 'Bolivia',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:04:23',
                'updated_at' => '2025-07-02 23:04:23',
                'categoria_id' => 2,
            ],
            // Departamentos
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-001',
                'catalogo_descripcion' => 'La paz',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:04:56',
                'updated_at' => '2025-07-02 23:04:56',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-002',
                'catalogo_descripcion' => 'Oruro',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:05:09',
                'updated_at' => '2025-07-02 23:05:09',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-003',
                'catalogo_descripcion' => 'Santa Cruz',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:05:23',
                'updated_at' => '2025-07-02 23:05:23',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-004',
                'catalogo_descripcion' => 'Potosi',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:05:52',
                'updated_at' => '2025-07-02 23:05:52',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-005',
                'catalogo_descripcion' => 'Cochabamba',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:06:06',
                'updated_at' => '2025-07-02 23:06:06',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-006',
                'catalogo_descripcion' => 'Tarija',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:06:27',
                'updated_at' => '2025-07-02 23:06:27',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-007',
                'catalogo_descripcion' => 'Beni',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:06:57',
                'updated_at' => '2025-07-02 23:06:57',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-008',
                'catalogo_descripcion' => 'Pando',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:07:19',
                'updated_at' => '2025-07-02 23:07:19',
                'categoria_id' => 3,
            ],
            [
                'catalogo_parent' => 'P-001',
                'catalogo_codigo' => 'D-009',
                'catalogo_descripcion' => 'Chuquisaca',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:07:33',
                'updated_at' => '2025-07-02 23:07:33',
                'categoria_id' => 3,
            ],
            // Ciudades
            [
                'catalogo_parent' => 'D-001',
                'catalogo_codigo' => 'C-001',
                'catalogo_descripcion' => 'Nuestra señora de La paz',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:07:58',
                'updated_at' => '2025-07-04 21:08:57',
                'categoria_id' => 1,
            ],
            [
                'catalogo_parent' => 'D-001',
                'catalogo_codigo' => 'C-002',
                'catalogo_descripcion' => 'El Alto',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:08:16',
                'updated_at' => '2025-07-02 23:08:16',
                'categoria_id' => 1,
            ],
            [
                'catalogo_parent' => 'D-001',
                'catalogo_codigo' => 'C-003',
                'catalogo_descripcion' => 'Viacha',
                'catalogo_estado' => 1,
                'created_at' => '2025-07-02 23:08:26',
                'updated_at' => '2025-07-02 23:08:26',
                'categoria_id' => 1,
            ],
        ]);

    }
}
