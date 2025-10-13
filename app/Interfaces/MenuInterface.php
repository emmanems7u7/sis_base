<?php

namespace App\Interfaces;
use App\Models\Menu;

interface MenuInterface
{
    public function CrearMenu($request);
    public function CrearSeccion($request);
    public function ObtenerMenuPorSeccion($seccion_id);

    function eliminarSeccionDeSeeder(int $id);

    public function eliminarMenuDeSeeders(Menu $menu);



}
