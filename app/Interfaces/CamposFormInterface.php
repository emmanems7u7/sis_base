<?php

namespace App\Interfaces;

interface CamposFormInterface
{
    public function obtenerOpcionesCompletas($campos);

    public function CrearCampoForm($request, $formulario);
    public function EditarCampoForm($request, $campo);
}
