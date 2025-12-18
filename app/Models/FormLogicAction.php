<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormLogicAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id',
        'form_ref_id',
        'tipo_accion',
        'parametros',
    ];

    protected $casts = [
        'parametros' => 'array',
    ];

    // Relaciones
    public function rule()
    {
        return $this->belongsTo(FormLogicRule::class, 'rule_id');
    }

    public function formularioDestino()
    {
        return $this->belongsTo(Formulario::class, 'form_ref_id');
    }

    public function conditions()
    {
        return $this->hasMany(FormLogicCondition::class, 'action_id');
    }

    // Accesores para obtener descripciones desde el catálogo
    public function getTipoAccionCatalogoAttribute()
    {
        return strtolower(
            Catalogo::where('catalogo_codigo', $this->tipo_accion)
                ->value('catalogo_descripcion') ?? 'No encontrado'
        );
    }

    /**
     * Métodos auxiliares para acceder a datos dentro de parametros
     * Ejemplo:
     * $action->param('formulario_relacion_seleccionado')
     */
    public function param($key, $default = null)
    {
        return $this->parametros[$key] ?? $default;
    }






    public function getCampoDestinoAttribute()
    {
        $campoId = $this->parametros['campo_ref_id'] ?? null;
        return $campoId ? CamposForm::find($campoId) : null;
    }

    public function getCampoOrigenAttribute()
    {
        $campoId = $this->parametros['campo_origen_id'] ?? null;
        return $campoId ? CamposForm::find($campoId) : null;
    }


    public function getOperacionCatalogoAttribute()
    {
        $operacion = $this->parametros['operacion'] ?? null;

        if (!$operacion) {
            return $this->parametros['tipo_accion_text'] ?? null;
        }

        $descripcion = Catalogo::where('catalogo_codigo', $operacion)
            ->value('catalogo_descripcion') ?? 'no encontrado';

        return strtolower($descripcion);
    }


}
