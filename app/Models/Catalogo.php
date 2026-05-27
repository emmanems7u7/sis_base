<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $fillable = [
        'categoria_id',
        'catalogo_parent',
        'catalogo_codigo',
        'catalogo_descripcion',
        'catalogo_estado',
    ];
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function getDependenciaAttribute()
    {
        return $this->parent?->catalogo_descripcion ?? 'S/N';
    }

    public function parent()
    {
        return $this->belongsTo(
            Catalogo::class,
            'catalogo_parent',
            'catalogo_codigo'
        );
    }
}
