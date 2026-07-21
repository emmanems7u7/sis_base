<?php

namespace App\Reportes;


use Illuminate\Database\Eloquent\Builder;


class FilterBuilder
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
        array $ruta,
        array $filter
    ) {


        $ultimoNodo = end($ruta);



        $alias =
            'filtro_' . $ultimoNodo->campo->id;



        $valor =
            $filter['valor'];



        $tipo =
            $filter['tipo'];



        if ($tipo == 'rango') {


            if (isset($valor['desde'])) {


                $this->query->where(
                    $alias . '.valor',
                    '>=',
                    $valor['desde']
                );


            }



            if (isset($valor['hasta'])) {


                $this->query->where(
                    $alias . '.valor',
                    '<=',
                    $valor['hasta']
                );


            }


        }



        if ($tipo == 'igual') {


            $this->query->where(
                $alias . '.valor',
                $valor
            );


        }



        if ($tipo == 'contiene') {


            $this->query->where(
                $alias . '.valor',
                'like',
                "%{$valor}%"
            );


        }



        return $this->query;

    }


}