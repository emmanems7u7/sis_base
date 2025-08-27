<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    protected $fillable = [
        'token',
        'tipo',
        'estado'
    ];

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}
