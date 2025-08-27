<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IaUsage extends Model
{
    protected $table = 'ia_usage';

    protected $fillable = ['usuario', 'tipo_consulta', 'tokens_usados'];
}
