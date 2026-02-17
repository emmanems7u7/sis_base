<?php

namespace Database\Seeders\GEN;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Database\Seeders\Traits\RunsOnce;

class FormulariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        $categoria = Categoria::create([
            'nombre' => 'Estado Formulario',
            'descripcion' => 'Listado de estados del formulario',
            'estado' => 1
        ]);

        $estados = ['borrador', 'publicado', 'archivado'];

        foreach ($estados as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'EFORM-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
