<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'slug', 'estado', 'config'];
    protected $casts = [
        'config' => 'array',
    ];
    public function campos()
    {
        return $this->hasMany(CamposForm::class, 'form_id');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestasForm::class, 'form_id');
    }
    public function getEstadoNombreAttribute()
    {
        $descripcion = Catalogo::where('catalogo_codigo', $this->estado)
            ->value('catalogo_descripcion') ?? 'No encontrado';

        return $descripcion;
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'formulario_modulo')
            ->withPivot(['configuracion', 'activo'])
            ->withTimestamps();
    }

}
