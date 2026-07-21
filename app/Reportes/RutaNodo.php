<?php

namespace App\Reportes;

use App\Models\CamposForm;
use App\Models\Formulario;

class RutaNodo
{
    public int $indice;

    public CamposForm $campo;

    public Formulario $formulario;

    public ?Formulario $formularioRelacion = null;

    public ?CamposForm $campoClave = null;

    public bool $esRelacion = false;

    public ?RutaNodo $anterior = null;

    public ?RutaNodo $siguiente = null;
    public ?string $alias = null;
}