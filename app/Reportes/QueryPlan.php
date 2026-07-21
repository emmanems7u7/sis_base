<?php

namespace App\Reportes;


class QueryPlan
{

    protected array $items = [];


    public function agregar(
        array $ruta,
        array $filtro = []
    ) {


        $this->items[] = [

            'ruta' => $ruta,

            'filtro' => $filtro

        ];

    }



    public function obtener()
    {
        return $this->items;
    }



    public function obtenerRutas()
    {

        return collect($this->items)

            ->pluck('ruta')

            ->toArray();

    }



    public function obtenerFiltros()
    {

        return collect($this->items)

            ->pluck('filtro')

            ->filter()

            ->values()

            ->toArray();

    }


}