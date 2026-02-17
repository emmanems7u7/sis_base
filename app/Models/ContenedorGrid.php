<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContenedorGrid extends Model
{
    protected $fillable = ['nombre', 'role_id'];


    public function filas()
    {
        return $this->hasMany(Fila::class)->orderBy('posicion');
    }


    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
