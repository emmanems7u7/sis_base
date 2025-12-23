<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaCorreo extends Model
{

    protected $fillable = [
        'nombre',
        'archivo',
        'estado'
    ];
}
