<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Catalogo;
use Database\Seeders\Traits\RunsOnce;

class TipoAccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use RunsOnce;
    protected function handle()
    {
        // Crear la categoría para tipos de acción
        $categoriaAcciones = Categoria::create([
            'nombre' => 'Tipos de Acción',
            'descripcion' => 'Tipos de acciones que se pueden aplicar en reglas de formulario',
            'estado' => 1
        ]);

        $tiposAccion = [
            'modificar_campo',
            'modificar_campos',
            'enviar_email',
            'crear_registro',
            'crear_registros',
            'notificacion',
            'actualizar_estado',
            'duplicar_registro',
            'asignar_usuario',
            'enviar_sms',
            'ejecutar_webhook',
            'generar_reporte',
        ];

        foreach ($tiposAccion as $index => $tipo) {
            Catalogo::create([
                'categoria_id' => $categoriaAcciones->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => 'TAC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'catalogo_descripcion' => $tipo,
                'catalogo_estado' => 1,
            ]);
        }
    }
}
