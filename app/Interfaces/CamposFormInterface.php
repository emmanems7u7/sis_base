<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CamposFormInterface
{
    public function obtenerOpcionesCompletas($campos);

    public function CrearCampoForm($request, $formulario);
    public function EditarCampoForm($request, $campo);
    public function CamposFormCat($campos, $limit = 100);
    public function ProcesarCampo($campo, $limit = 20, $offset = 0);
    public function guardarCampo($campo, $respuesta_id, $datosFormulario, $form, $prefix = null);
    public function guardarValorSimple($campo, $respuestaId, $valor);
    public function actualizarCampo($campo, $respuesta_id, $datosFormulario, $form, $prefix = null);
}
