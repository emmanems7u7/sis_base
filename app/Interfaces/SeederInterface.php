<?php

namespace App\Interfaces;

use App\Models\Catalogo;
use App\Models\Categoria;
use App\Models\Menu;
use App\Models\Seccion;
use Spatie\Permission\Models\Permission;

interface SeederInterface
{
    public function guardarEnSeederMenu(Menu $menu);
    public function eliminarDeSeederMenu(Menu $menu);
    public function guardarEnSeederSeccion(Seccion $seccion);
    public function eliminarDeSeederSeccion(Seccion $seccion);

    public function guardarEnSeederPermiso(Permission $permiso, $id_relacion = 0);
    public function eliminarDeSeederPermiso(Permission $permiso);

    public function guardarEnSeederCategoria(Categoria $categoria);
    public function eliminarDeSeederCategoria(Categoria $categoria);
    public function guardarEnSeederCatalogo(Catalogo $catalogo);
    public function eliminarDeSeederCatalogo(Catalogo $catalogo);
}
