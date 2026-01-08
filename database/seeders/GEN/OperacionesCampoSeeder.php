<?php

namespace Database\Seeders\GEN;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Support\Str;
class OperacionesCampoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Crear la categoría para operaciones
        $categoria = Categoria::create([
            'nombre' => 'Operaciones de Campo',
            'descripcion' => 'Listado de operaciones que se pueden aplicar a los campos de formulario',
            'estado' => 1
        ]);

        $operaciones = [
            'sumar',
            'restar',
            'actualizar',
            'copiar',
            'asignar',
            'concatenar',
            'multiplicar',
            'dividir',
            'limpiar',
            'incrementar_fecha',
            'decrementar_fecha'
        ];

        foreach ($operaciones as $index => $descripcion) {
            Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'OPC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $descripcion,
                'catalogo_estado' => 1,
                'accion_usuario' => 'seeder_' . Str::random(5),
            ]);
        }
    }
}
