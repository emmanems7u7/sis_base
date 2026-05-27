<?php

namespace App\Repositories;

use App\Interfaces\ModuloInterface;
use App\Models\Modulo;

class ModuloRepository implements ModuloInterface
{
    public function GetModuloById($modulo)
    {
        return Modulo::find($modulo);
    }
}
