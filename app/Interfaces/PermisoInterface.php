<?php

namespace App\Interfaces;
use Spatie\Permission\Models\Permission;
interface PermisoInterface
{
    public function GetPermisosTipo($tipo);
    public function GetPermisoTipo($id, $tipo);
    public function GetPermisoMenu($id, $rol_id);
    public function CrearPermiso($request);
    public function Store_Permiso(string $nombre, string $tipo, ?int $idRelacion = null, bool $soloCrear = false);
    public function EditarPermiso($request, $permission);

    public function eliminarDeSeederPermiso($permiso);



}
