<?php

namespace App\Interfaces;

use App\Models\FormLogicRule;
use App\Models\RespuestasForm;

interface FormLogicInterface
{
    public function CrearRegla($request);
    public function EditarRegla($request, $form_logic);
    public function ejecutarLogica($reglas, $respuestas, $evento, $usuario, $esCascada = false): array;
    public function ejecutarAccion($respuestas, $action, $usuario, $esMultiple, $esCascada = false): array;

    public function EjecutarReglaLogica($reglas, array $respuestas, string $evento, $usuario, $url, $esCascada = false);

    public function validarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action);
    public function ValidarLogica($respuesta, $filasSeleccionadas, $evento);
    public function EjecutarAcciones($agrupadas, $evento);
    public function LogicaEliminarRespuesta($evento, $respuesta);


}
