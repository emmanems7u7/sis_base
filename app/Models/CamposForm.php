<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CamposForm extends Model
{
    protected $fillable = [
        'form_id',
        'form_ref_id',
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
    protected $appends = ['campo_nombre'];
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
        return strtolower(
            $this->tipoCatalogo?->catalogo_descripcion ?? 'no encontrado'
        );
    }

    public function tipoCatalogo()
    {
        return $this->belongsTo(
            Catalogo::class,
            'tipo',
            'catalogo_codigo'
        );
    }
    public function opciones_catalogo()
    {
        return $this->hasMany(Catalogo::class, 'categoria_id', 'categoria_id');
    }


    // Relación al formulario de referencia (de donde se obtienen las opciones)
    public function formularioReferencia()
    {
        return $this->belongsTo(Formulario::class, 'form_ref_id');
    }


    // Si el campo está vinculado a otro formulario,
    // obtener sus respuestas como opciones posibles
    public function opcionesFormularioQuery()
    {
        return $this->formularioReferencia
            ? $this->formularioReferencia->respuestas()->with('camposRespuestas.campo')
            : collect();
    }




}
