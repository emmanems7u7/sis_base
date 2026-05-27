<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'slug', 'estado', 'campos_columnas', 'config'];
    protected $casts = [
        'config' => 'array',
    ];
    public function campos()
    {
        return $this->hasMany(CamposForm::class, 'form_id');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestasForm::class, 'form_id');
    }
    public function getEstadoNombreAttribute()
    {
        return $this->estadoCatalogo?->catalogo_descripcion
            ?? 'No encontrado';
    }

    public function estadoCatalogo()
    {
        return $this->belongsTo(
            Catalogo::class,
            'estado',
            'catalogo_codigo'
        );
    }

    public function ColumnasCatalogo()
    {
        return $this->belongsTo(
            Catalogo::class,
            'campos_columnas',
            'catalogo_codigo'
        );
    }
    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'formulario_modulo')
            ->withPivot(['configuracion', 'activo'])
            ->withTimestamps();
    }
    public function getGridAttribute()
    {
        $config = $this->ColumnasCatalogo?->catalogo_descripcion
            ?? 'movil-1|desktop-1';

        list($movil, $desktop) = explode('|', $config);

        $movilCols = (int) str_replace('movil-', '', $movil);
        $desktopCols = (int) str_replace('desktop-', '', $desktop);

        $colMovilSize = intval(12 / max(1, $movilCols));
        $colDesktopSize = intval(12 / max(1, $desktopCols));

        return "col-{$colMovilSize} col-md-{$colDesktopSize}";
    }
}
