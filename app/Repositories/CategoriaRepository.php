<?php

namespace App\Repositories;

use App\Interfaces\CategoriaInterface;
use App\Models\Categoria;

class CategoriaRepository implements CategoriaInterface
{

    public function GetAll()
    {
        return Categoria::all();
    }


}
