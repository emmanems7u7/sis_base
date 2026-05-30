<?php

namespace App\Interfaces;

interface RespuestasCampoInterface
{
    public function GetRespCampoByIdValor($campoId, $valor);
    public function fila($request);
    public function filaDesdeArray($respuesta, array $registroData, $campos);
    public function filaDesdeRespuesta($respuesta, $campos);
    public function obtenerRelacionMultiple($form_id, $form_id2);

}
