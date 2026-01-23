<?php

namespace App\Interfaces;

interface RespuestasFormInterface
{
    public function GetHumanRules($rules);
    public function fila($request);
    public function validacion($campos, $respuestaId = null, $modo = 'store');

    public function GeneraPlantilla($campos, $form);

    function validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);
    public function EliminarArchivos($respuesta);


}
