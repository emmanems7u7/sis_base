<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Columna extends Model
{
    protected $fillable = ['fila_id', 'ancho', 'posicion', 'widget_id'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
    public function getClasesBootstrapAttribute()
    {
        $lg = max(1, min(12, (int) $this->ancho));

        $md = ($lg <= 4) ? 6 : 12;

        return implode(' ', [
            'col-12',          // xs
            'col-sm-12',       // sm
            "col-md-{$md}",    // md
            "col-lg-{$lg}",    // lg
            "col-xl-{$lg}",    // xl
            "col-xxl-{$lg}",   // xxl
        ]);
    }
}
