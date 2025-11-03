<?php

namespace App\Interfaces;
use App\Models\RespuestasForm;

interface FormLogicInterface
{
    public function ejecutarLogica(RespuestasForm $respuesta, $filasSeleccionadas, string $evento);
    public function ejecutarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action);
}
