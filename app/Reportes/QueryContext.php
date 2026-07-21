<?php

namespace App\Reportes;

class QueryContext
{

    protected array $aliases = [];


    public function registrar(
        string $clave,
        string $alias
    ) {

        $this->aliases[$clave] = $alias;

    }


    public function obtener(
        string $clave
    ): ?string {

        return $this->aliases[$clave] ?? null;

    }


    public function existe(
        string $clave
    ): bool {

        return isset(
            $this->aliases[$clave]
        );

    }


    public function todos()
    {

        return $this->aliases;

    }

}