<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'modulo_padre_id',
        'configuracion',
        'activo'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
    ];

    public function padre()
    {
        return $this->belongsTo(Modulo::class, 'modulo_padre_id');
    }

    public function hijos()
    {
        return $this->hasMany(Modulo::class, 'modulo_padre_id');
    }

    public function formularios()
    {
        return $this->belongsToMany(Formulario::class, 'formulario_modulo')
            ->withPivot(['configuracion', 'activo'])
            ->withTimestamps();
    }
}
