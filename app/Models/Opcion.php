<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opcion extends Model
{
    protected $fillable = ['categoria_id', 'etiqueta', 'valor', 'orden'];

}
