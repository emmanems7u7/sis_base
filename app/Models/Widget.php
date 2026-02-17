<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Widget extends Model
{
    protected $fillable = [
        'formulario_id',
        'modulo_id',
        'nombre',
        'tipo',
        'configuracion',
        'activo'
    ];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean',
    ];



    public function tipoNombre()
    {
        return Catalogo::where('catalogo_codigo', $this->tipo)->value('catalogo_descripcion') ?? 'Desconocido';
    }

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }
    public function getRespuestasCountAttribute()
    {
        return $this->formulario ? $this->formulario->respuestas()->count() : 0;
    }
}
