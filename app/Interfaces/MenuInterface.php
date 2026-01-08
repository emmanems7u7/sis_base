<?php

namespace App\Interfaces;
use App\Models\Menu;
use App\Models\Seccion;

interface MenuInterface
{
    public function CrearMenu($request);
    public function CrearSeccion($request);
    public function ObtenerMenuPorSeccion($seccion_id);

    public function eliminarDeSeederSeccion(Seccion $seccion);


}
