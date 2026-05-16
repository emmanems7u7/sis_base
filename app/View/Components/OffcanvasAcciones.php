<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OffcanvasAcciones extends Component
{
    public $id;
    public $titulo;
    public $icono;
    public $contenidoId;
    public $templateId;
    public $height;

    public function __construct(
        $id,
        $titulo = 'Acciones Disponibles',
        $icono = 'fas fa-bolt',
        $contenidoId = 'accionesContenido',
        $templateId = 'acciones-template',
        $height = '135px'
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->icono = $icono;
        $this->contenidoId = $contenidoId;
        $this->templateId = $templateId;
        $this->height = $height;
    }

    public function render(): View|Closure|string
    {
        return view('components.offcanvas-acciones');
    }
}