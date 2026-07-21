<?php

namespace App\Reportes;


class CampoResolver
{

    protected QueryContext $context;


    public function __construct(
        QueryContext $context
    ) {
        $this->context = $context;
    }



    public function resolver(
        array $ruta
    ): array {


        $ultimoNodo = end($ruta);


        /*
        |--------------------------------------------------------------------------
        | Campo pertenece al formulario principal
        |--------------------------------------------------------------------------
        */

        if (!$ultimoNodo->formularioRelacion) {


            return [
                'tabla' => 'respuestas_forms',
                'alias' => null,
                'campo_id' => $ultimoNodo->campo->id
            ];


        }



        /*
        |--------------------------------------------------------------------------
        | Campo pertenece a formulario relacionado
        |
        | Ej:
        |
        | 15.12
        |
        | Ventas.fecha
        |--------------------------------------------------------------------------
        */


        $aliasFormulario =
            'rel_' .
            $ultimoNodo->formulario->id;



        return [
            'tabla' => 'respuestas_campos',
            'alias' => 'filtro_' . $ultimoNodo->campo->id,
            'formulario_alias' => $aliasFormulario,
            'campo_id' => $ultimoNodo->campo->id
        ];


    }

}