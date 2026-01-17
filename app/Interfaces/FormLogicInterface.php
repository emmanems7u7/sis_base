<?php

namespace App\Interfaces;

use App\Models\FormLogicRule;
use App\Models\RespuestasForm;

interface FormLogicInterface
{

    public function CrearRegla($request);
    public function EditarRegla($request, $form_logic);

    public function ejecutarLogica(RespuestasForm $respuesta, $filasSeleccionadas, $evento, $usuario);

    public function ejecutarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action, $usuario);

    public function validarAccion(
        RespuestasForm $respuestaOrigen,
        $filasSeleccionadas,
        $action
    );

    public function ValidarLogica(RespuestasForm $respuesta, $filasSeleccionadas, $evento);
}
