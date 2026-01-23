<?php

namespace Database\Seeders\GEN;

use App\Models\Catalogo;
use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TipoArchivosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Tipo de documentos para carga',
            'descripcion' => 'Listado de Tipos de documentos para carga',
            'estado' => 1
        ]);
        $tiposDocumentoC = [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'txt',
            'csv',
            'zip',
            'rar',
            'odt',
            'ods'
        ];


        foreach ($tiposDocumentoC as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TDC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
