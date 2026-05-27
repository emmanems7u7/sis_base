<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormConfiguration extends Model
{
    protected $fillable = [
        'formulario_id',
        'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class);
    }

    public function configuration()
    {
        return $this->hasOne(FormConfiguration::class);
    }
}
