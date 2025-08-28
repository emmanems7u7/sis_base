<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestasForm extends Model
{
    protected $fillable = ['form_id', 'user_id', 'actor_id'];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'form_id');
    }

    public function camposRespuestas()
    {
        return $this->hasMany(RespuestasCampo::class, 'respuesta_id');
    }
}
