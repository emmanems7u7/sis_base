<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormLogicExecution extends Model
{

    protected $fillable = [

        'rule_id',
        'estado',
        'inicio',
        'fin',
        'registros_afectados',
        'mensaje',
        'error',
        'resultado',

    ];


    protected $casts = [

        'resultado' => 'array',
        'inicio' => 'datetime',
        'fin' => 'datetime',

    ];



    public function rule()
    {
        return $this->belongsTo(FormLogicRule::class, 'rule_id');
    }


}