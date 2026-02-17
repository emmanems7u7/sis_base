<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fila extends Model
{
    protected $fillable = ['contenedor_grid_id', 'posicion'];
    public function columnas()
    {
        return $this->hasMany(Columna::class)->orderBy('posicion');
    }
}
