<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CamposForm extends Model
{
    protected $fillable = [
        'form_id',
        'nombre',
        'etiqueta',
        'tipo',
        'posicion',
        'requerido',
        'categoria_id',
        'config'
    ];

    protected $casts = [
        'config' => 'array',
        'requerido' => 'boolean'
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'form_id');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestasCampo::class, 'cf_id');
    }
    public function getCampoNombreAttribute()
    {
        $descripcion = Catalogo::where('catalogo_codigo', $this->tipo)
            ->value('catalogo_descripcion') ?? 'No encontrado';

        return strtolower($descripcion);
    }
}
