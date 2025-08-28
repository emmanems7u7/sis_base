<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class ChatMensaje extends Model
{
    use HasFactory;

    protected $fillable = [
        'anon_id',
        'role',
        'contenido',
        'tipo_consulta',
    ];
}
