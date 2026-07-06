<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormularioAsociacion extends Model
{
    protected $fillable = [
        'formularios',
        'config'
    ];

    protected $casts = [
        'config' => 'array',
        'formularios' => 'array',

    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function getFormulariosCompletosAttribute()
    {
        if (!$this->formularios) {
            return collect();
        }

        $ids = collect($this->formularios)->pluck('id')->toArray();

        $formularios = Formulario::whereIn('id', $ids)->get();

        return $formularios->map(function ($form) {
            $meta = collect($this->formularios)->firstWhere('id', $form->id);
            return [
                'formulario' => $form,
                'es_principal' => $meta['es_principal'] ?? 0
            ];
        });
    }
}
