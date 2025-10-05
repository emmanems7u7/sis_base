<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestasCampo extends Model
{
    protected $fillable = [
        'respuesta_id',
        'cf_id',
        'valor'
    ];
    public function respuesta()
    {
        return $this->belongsTo(RespuestasForm::class, 'respuesta_id');
    }

    public function campo()
    {
        return $this->belongsTo(CamposForm::class, 'cf_id');
    }
}
