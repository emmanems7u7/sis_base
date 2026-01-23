<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionCredenciales extends Model
{
    protected $fillable = [
        'conf_long_min',
        'conf_long_max',
        'conf_req_upper',
        'conf_req_num',
        'conf_req_esp',
        'conf_duracion_min',
        'conf_duracion_max',
        'conf_tiempo_bloqueo',
        'conf_defecto',
    ];
}
