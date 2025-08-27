<?php

namespace App\Interfaces;
use App\Models\Menu;

interface MenuInterface
{
    public function CrearMenu($request);
    public function CrearSeccion($request);
    public function ObtenerMenuPorSeccion($seccion_id);
    public function eliminarDeSeederMenu(Menu $menu);
    function eliminarSeccionDeSeeder(int $id);

    function eliminarMenuDeSeeder(int $id);


}
