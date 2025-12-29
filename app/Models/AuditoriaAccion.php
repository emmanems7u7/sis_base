<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaAccion extends Model
{

    protected $fillable = [
        'action_id',
        'tipo_accion',
        'usuario_id',
        'estado',
        'mensaje',
        'detalle',
        'errores',
    ];
    protected $casts = [
        'detalle' => 'array',
        'errores' => 'array',
    ];

    public function getNombreUsuarioAttribute(): string
    {
        $usuario = User::find($this->usuario_id);
        return $usuario->NombreCompleto;
    }

}
