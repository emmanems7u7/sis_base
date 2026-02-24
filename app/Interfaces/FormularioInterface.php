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

    public function guardarCampo($campo, $respuesta_id, Request $request, $form);
    public function guardarArchivoGenerico($campo, $respuestaId, $form, $ruta);
    public function guardarValorSimple($campo, $respuestaId, $valor);
    public function validarOpcionesCatalogo($campos, $request);
    public function CamposFormCat($campos, $limit = 100);
    public function ProcesarCampo($campo, $limit = 20, $offset = 0);

    public function convertirValorParaFiltro($campo, $valorUsuario);

    public function obtenerValorReal(CamposForm $campo, $valorUsuario);

    public function procesarFormularioConFiltros($formulario, Request $request, $pageName = null);
    public function resolverValor($campoRespOrCampo, $valor = null);

    public function generar_informacion_export($respuestas, $formulario);
    public function procesarCamposRespuesta($respuesta, $formulario);


}
