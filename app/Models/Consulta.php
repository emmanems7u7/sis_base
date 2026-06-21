<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $fillable = [
        'nombre',
        'formulario_id',
        'configuracion',
        'activo'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean'
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class);
    }
}
