<?php

namespace App\Interfaces;

use App\Models\CamposForm;
use Illuminate\Http\Request;

interface FormularioInterface
{
    public function CrearFormulario($request);
    public function EditarFormulario($request, $formulario);

    public function crearRespuesta($form);
    public function CrearRespuestaGrupo();

    public function guardarArchivoGenerico($campo, $respuestaId, $form, $ruta);

    public function convertirValorParaFiltro($campo, $valorUsuario);

    public function obtenerValorReal(CamposForm $campo, $valorUsuario);

    public function procesarFormularioConFiltros($formulario, Request $request, $pageName = null);
    public function resolverValor($campoRespOrCampo, $valor = null);

    public function generar_informacion_export($respuestas, $formulario);
    public function procesarCamposRespuesta($respuesta, $formulario);
    public function GetData($request, $formPrefix, $rules, $registro = null);

    public function obtenerFormularios($form, $moduloModelo);
    public function obtenerFormulariosDelGrupo($formularioId, $moduloId);
    public function GetFormRelacion($form, $relacion);
    public function GetFormById($form);
    public function GetFormAll();
    public function EliminarArchivos($respuesta);

}
