<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class FormLogicRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'nombre',
        'evento',
        'activo',
        'parametros'
    ];

    protected $casts = [
        'parametros' => 'array',
        'activo' => 'boolean'
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'form_id');
    }

    public function actions()
    {
        return $this->hasMany(FormLogicAction::class, 'rule_id');
    }
}
