<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestasForm extends Model
{
    protected $fillable = ['form_id', 'actor_id'];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'form_id');
    }

    public function camposRespuestas()
    {
        return $this->hasMany(RespuestasCampo::class, 'respuesta_id');
    }


    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
    public function grupos()
    {
        return $this->belongsToMany(
            RespuestasGrupo::class,
            'respuestas_grupos_detalle',
            'respuesta_id',
            'grupo_id'
        );
    }

    public function esDeGrupo($grupoId)
    {
        return $this->grupos()->where('grupo_id', $grupoId)->exists();
    }
}
