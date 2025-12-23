<?php

namespace App\Interfaces;

use App\Models\CamposForm;
use Illuminate\Http\Request;

interface FormularioInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);

    public function crearRespuesta($form);
    public function guardarCampo($campo, $respuesta_id, Request $request, $form);
    public function guardarArchivoGenerico($campo, $respuestaId, $form, $ruta);
    public function guardarValorSimple($campo, $respuestaId, $valor);
    public function validarOpcionesCatalogo($campos, $request);
    public function CamposFormCat($campos, $limit = 100);
    public function convertirValorParaFiltro($campo, $valorUsuario);

    public function obtenerValorReal(CamposForm $campo, $valorUsuario);



}
