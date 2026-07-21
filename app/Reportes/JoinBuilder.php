<?php

namespace App\Reportes;

use Illuminate\Database\Eloquent\Builder;

class JoinBuilder
{

    protected Builder $query;


    protected QueryContext $context;


    public function __construct(
        Builder $query,
        QueryContext $context
    ) {
        $this->query = $query;
        $this->context = $context;
    }



    public function apply(
        QueryPlan $plan
    ): Builder {


        foreach ($plan->obtener() as $item) {


            $ruta = $item['ruta'];


            foreach ($ruta as $nodo) {


                if ($nodo->esRelacion) {

                    $this->crearRelacion($nodo);

                }

            }


            if (!empty($item['filtro'])) {

                $this->aplicarFiltro(
                    $ruta,
                    $item['filtro']
                );

            }


        }


        return $this->query;

    }




    protected function crearRelacion(
        RutaNodo $nodo
    ) {


        /*
        |--------------------------------------------------------------------------
        | Campo relación del formulario origen
        |
        | Ej:
        | Carrito.numero_venta
        |--------------------------------------------------------------------------
        */


        $aliasCampo =
            'rel_campo_' . $nodo->campo->id;



        if (!$this->context->existe($aliasCampo)) {


            $this->query->leftJoin(
                'respuestas_campos as ' . $aliasCampo,
                function ($join) use ($aliasCampo, $nodo) {


                    $join->on(
                        $aliasCampo . '.respuesta_id',
                        '=',
                        'respuestas_forms.id'
                    );


                    $join->where(
                        $aliasCampo . '.cf_id',
                        $nodo->campo->id
                    );


                }
            );


            $this->context->registrar(
                $aliasCampo,
                $aliasCampo
            );


        }



        /*
        |--------------------------------------------------------------------------
        | Respuesta formulario relacionado
        |
        | Ej:
        | Ventas Registradas
        |--------------------------------------------------------------------------
        */


        $aliasRespuesta =
            'rel_' . $nodo->formularioRelacion->id;



        if (!$this->context->existe($aliasRespuesta)) {


            $this->query->join(
                'respuestas_forms as ' . $aliasRespuesta,
                function ($join) use ($aliasRespuesta, $nodo) {


                    $join->on(
                        $aliasRespuesta . '.form_id',
                        '=',
                        \DB::raw(
                            $nodo->formularioRelacion->id
                        )
                    );


                }
            );


            $this->context->registrar(
                $aliasRespuesta,
                $aliasRespuesta
            );


        }



        /*
        |--------------------------------------------------------------------------
        | Campo clave del formulario relacionado
        |
        | Ventas.numero_venta
        |--------------------------------------------------------------------------
        */


        $aliasClave =
            'clave_' . $nodo->formularioRelacion->id;



        if (!$this->context->existe($aliasClave)) {


            $this->query->join(
                'respuestas_campos as ' . $aliasClave,
                function ($join) use ($aliasClave, $aliasRespuesta, $nodo, $aliasCampo) {


                    $join->on(
                        $aliasClave . '.respuesta_id',
                        '=',
                        $aliasRespuesta . '.id'
                    );


                    $join->where(
                        $aliasClave . '.cf_id',
                        $nodo->campoClave->id
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Relación real
                    |
                    | carrito.numero_venta =
                    | ventas.numero_venta
                    |--------------------------------------------------------------------------
                    */


                    $join->whereColumn(
                        $aliasClave . '.valor',
                        '=',
                        $aliasCampo . '.valor'
                    );


                }
            );


            $this->context->registrar(
                $aliasClave,
                $aliasClave
            );


        }


    }




    protected function aplicarFiltro(
        array $ruta,
        array $filtro
    ) {


        if (!$filtro) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | El último nodo es el campo real a filtrar
        |
        | Ej:
        | fecha
        |--------------------------------------------------------------------------
        */


        $ultimoNodo =
            end($ruta);



        $campo =
            $ultimoNodo->campo;



        /*
        |--------------------------------------------------------------------------
        | Alias del formulario relacionado
        |--------------------------------------------------------------------------
        */


        if ($ultimoNodo->formulario->id) {


            $alias =
                'rel_' . $ultimoNodo->formulario->id;


            $aliasCampo =
                'filtro_' . $campo->id;



            $this->query->join(
                'respuestas_campos as ' . $aliasCampo,
                function ($join) use ($aliasCampo, $alias, $campo) {


                    $join->on(
                        $aliasCampo . '.respuesta_id',
                        '=',
                        $alias . '.id'
                    );


                    $join->where(
                        $aliasCampo . '.cf_id',
                        $campo->id
                    );


                }
            );



            $this->aplicarCondicion(
                $aliasCampo,
                $filtro
            );

        }


    }




    protected function aplicarCondicion(
        string $alias,
        array $filtro
    ) {


        $tipo =
            $filtro['tipo'] ?? null;


        $valor =
            $filtro['valor'] ?? null;



        if ($tipo == 'rango') {


            if (!empty($valor['desde'])) {

                $this->query->where(
                    $alias . '.valor',
                    '>=',
                    $valor['desde']
                );

            }


            if (!empty($valor['hasta'])) {

                $this->query->where(
                    $alias . '.valor',
                    '<=',
                    $valor['hasta']
                );

            }


            return;

        }



        if ($tipo == 'igual') {


            $this->query->where(
                $alias . '.valor',
                $valor
            );


            return;

        }



        if ($tipo == 'contiene') {


            $this->query->where(
                $alias . '.valor',
                'like',
                "%{$valor}%"
            );


        }


    }

    protected function agregarCampoFiltro(
        RutaNodo $nodo
    ) {

        $alias =
            'filtro_' . $nodo->campo->id;



        if ($this->context->existe($alias)) {
            return;
        }



        $aliasFormulario =
            'rel_' . $nodo->formulario->id;



        $this->query->leftJoin(
            'respuestas_campos as ' . $alias,
            function ($join) use ($alias, $aliasFormulario, $nodo) {


                $join->on(
                    $alias . '.respuesta_id',
                    '=',
                    $aliasFormulario . '.id'
                );


                $join->where(
                    $alias . '.cf_id',
                    $nodo->campo->id
                );


            }
        );



        $this->context->registrar(
            $alias,
            $alias
        );

    }

}