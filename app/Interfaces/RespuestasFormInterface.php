<?php

namespace App\Interfaces;

interface RespuestasFormInterface
{
    public function GetHumanRules($rules);

    public function validacion($formulario, $campos, $respuestaId = null, $modo = 'store', $prefix = null);
    public function GeneraPlantilla($campos, $form);

    function validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

    public function normalizarRegistros(array $registros): array;
    public function procesarFormularioNormalDesdeArray($datosFormulario, $form, $campos, $prefix, $evento);
    public function procesarFormularioMultipleDesdeArray($datosFormulario, $form, $campos, $prefix, $grupo, $evento);

    public function cargarFormularioCompleto($formularioId);
    public function obtenerReglasHumanas($campos);
    public function ProcesarArchivo($archivo);
    public function procesarChunk($form);
    public function LogicaActualizacion($formId, $formPrefix, $respuestaTarget, $formularioModelo, $request, $evento);

}
