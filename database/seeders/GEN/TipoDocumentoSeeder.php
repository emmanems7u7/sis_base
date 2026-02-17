<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Database\Seeders\Traits\RunsOnce;

class TipoDocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        $categoria = Categoria::create([
            'nombre' => 'Tipo Documentos',
            'descripcion' => 'Listado de Tipos de documentos',
            'estado' => 1
        ]);
        $tiposDocumento = ['CI', 'Pasaporte', 'RUC', 'NIT', 'Licencia de conducir', 'DNI extranjero'];

        foreach ($tiposDocumento as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TD-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }


    }
}
