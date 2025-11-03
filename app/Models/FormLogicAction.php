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
        'campo_ref_id',
        'operacion',
        'valor',
        'parametros',
        'tipo_valor'
    ];

    protected $casts = [
        'parametros' => 'array',
    ];

    public function rule()
    {
        return $this->belongsTo(FormLogicRule::class, 'rule_id');
    }

    public function formularioDestino()
    {
        return $this->belongsTo(Formulario::class, 'form_ref_id');
    }

    public function campoDestino()
    {
        return $this->belongsTo(CamposForm::class, 'campo_ref_id');
    }

    public function conditions()
    {
        return $this->hasMany(FormLogicCondition::class, 'action_id');
    }

    public function getOperacionCatalogoAttribute()
    {
        $descripcion = Catalogo::where('catalogo_codigo', $this->operacion)
            ->value('catalogo_descripcion') ?? 'No encontrado';

        return strtolower($descripcion);
    }
}
