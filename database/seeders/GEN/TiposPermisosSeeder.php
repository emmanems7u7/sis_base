<?php

namespace Database\Seeders\GEN;


use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\RunsOnce;


class TiposPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {


        $categoria = Categoria::create([
            'nombre' => 'Tipos de permisos para roles',
            'descripcion' => 'Listado de Tipos de permisos para roles',
            'estado' => 1
        ]);

        $tipos = ['ver', 'crear', 'editar', 'guardar', 'actualizar', 'eliminar'];


        foreach ($tipos as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TIPR-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
