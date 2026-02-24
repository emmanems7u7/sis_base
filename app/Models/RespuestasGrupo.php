<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestasGrupo extends Model
{
    protected $fillable = ['actor_id', 'codigo'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function respuestas()
    {
        return $this->belongsToMany(
            RespuestasForm::class,
            'respuestas_grupos_detalle',
            'grupo_id',
            'respuesta_id'
        );
    }
}
