<?php

namespace App\Interfaces;

use App\Models\FormLogicRule;
use App\Models\RespuestasForm;

interface FormLogicInterface
{
    public function ejecutarLogica(RespuestasForm $respuesta, $filasSeleccionadas, $evento, $usuario);

    public function ejecutarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action, $usuario);

    public function validarAccion(
        RespuestasForm $respuestaOrigen,
        $filasSeleccionadas,
        $action
    );

    public function ValidarLogica(RespuestasForm $respuesta, $filasSeleccionadas, $evento);
}
