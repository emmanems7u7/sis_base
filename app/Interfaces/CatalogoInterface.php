<?php

namespace App\Interfaces;

use App\Models\Categoria;

interface CatalogoInterface
{
    public function GuardarCatalogo($request);
    public function EditarCatalogo($request, $catalogo);

    public function GuardarCategoria($request);
    public function EditarCategoria($request, $categoria);
    public function obtenerCatalogosPorCategoria($nombreCategoria, $soloActivos = false);
    public function getNombreCatalogo($catalogo_codigo);

    public function obtenerCatalogosPorCategoriaID($id, $soloActivos = false, $limit = null, $offset = 0);
    public function buscarPorDescripcion($categoriaId, $descripcion);

    public function eliminarDeSeederCategoria(Categoria $categoria);


}
