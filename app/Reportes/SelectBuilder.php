<?php

namespace App\Reportes;

use App\Models\Formulario;
use Illuminate\Database\Eloquent\Builder;


class SelectBuilder
{

    protected Formulario $formulario;
    protected Builder $query;
    protected QueryContext $context;


    public function __construct(
        Builder $query,
        QueryContext $context,
        Formulario $formulario
    ) {

        $this->query = $query;
        $this->context = $context;
        $this->formulario = $formulario;

    }



    public function aplicar(
        array $campos
    ) {


        $compiler = new RutaCompiler();


        foreach ($campos as $campo) {


            $ruta = $compiler->compile(
                $this->formulario,
                $campo
            );


            $this->seleccionar(
                $ruta
            );


        }


        return $this->query;

    }





    protected function seleccionar(
        array $ruta
    ) {


        $ultimoNodo = end($ruta);


        $campo = $ultimoNodo->campo;



        /*
        |--------------------------------------------------------------------------
        | Detectar si viene de una relación
        |
        | Ej:
        |
        | 15.11
        |
        | 15 = relación
        | 11 = campo relacionado
        |--------------------------------------------------------------------------
        */


        $nodoRelacion = null;


        foreach ($ruta as $nodo) {


            if ($nodo->esRelacion) {

                $nodoRelacion = $nodo;

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Campo dentro de formulario relacionado
        |
        | Ej:
        |
        | carrito.numero_venta
        |--------------------------------------------------------------------------
        */


        if ($nodoRelacion) {


            $aliasFormulario =
                'rel_' . $nodoRelacion->formularioRelacion->id;



            $this->agregarCampo(
                $campo,
                $aliasFormulario
            );


            return;

        }




        /*
        |--------------------------------------------------------------------------
        | Campo del formulario principal
        |
        | Ej:
        |
        | ventas.fecha
        |--------------------------------------------------------------------------
        */


        $this->agregarCampo(
            $campo,
            'respuestas_forms'
        );


    }





    protected function agregarCampo(
        $campo,
        string $aliasRespuesta
    ) {


        $aliasCampo =
            'select_campo_' . $campo->id;



        if (!$this->context->existe($aliasCampo)) {


            $this->query->leftJoin(
                'respuestas_campos as ' . $aliasCampo,
                function ($join) use ($aliasCampo, $aliasRespuesta, $campo) {


                    $join->on(
                        $aliasCampo . '.respuesta_id',
                        '=',
                        $aliasRespuesta . '.id'
                    );


                    $join->where(
                        $aliasCampo . '.cf_id',
                        $campo->id
                    );


                }
            );


            $this->context->registrar(
                $aliasCampo,
                $aliasCampo
            );


        }



        $this->query->addSelect([

            $aliasCampo . '.valor as ' . $campo->nombre

        ]);

    }


}