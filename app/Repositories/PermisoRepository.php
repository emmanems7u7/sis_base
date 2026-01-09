<?php
namespace App\Repositories;

use App\Interfaces\PermisoInterface;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Menu;
use App\Models\Seccion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


use App\Interfaces\SeederInterface;


class PermisoRepository extends BaseRepository implements PermisoInterface
{
    protected $permissions;
    protected $SeederRepository;



    public function __construct(SeederInterface $seederRepository)
    {
        parent::__construct();
        $this->permissions = Permission::all();
        $this->SeederRepository = $seederRepository;
    }
    public function GetPermisosTipo($tipo)
    {

        return $this->permissions->where('tipo', $tipo);
    }
    public function GetPermisoMenu($id, $rol_id)
    {

        $permission = Permission::findOrFail($id);

        $menus = Seccion::with('menus')->find($permission->id_relacion)->menus;

        if ($rol_id != -1) {
            $role = Role::find($rol_id);

        } else {
            $role = Role::all();

        }

        $permisos_menu = $permission->where('tipo', 'menu')->get();

        foreach ($permisos_menu as $permiso_menu) {
            foreach ($menus as $menu) {
                if ($permiso_menu->id_relacion == $menu->id) {

                    $permission = $permission->where('id_relacion', $menu->id)->where('tipo', 'menu')->first();

                    if ($rol_id != -1) {
                        if ($role->hasPermissionTo($permission)) {
                            $permission->check = true;
                        } else {
                            $permission->check = false;
                        }
                    }
                    $permisosPorTipo[] = $permission;

                }
            }

        }
        return $permisosPorTipo;
    }
    public function GetPermisoTipo($id, $tipo)
    {

    }
    function CrearPermiso($request)
    {
        $this->Store_Permiso($request->name, 'permiso', null, true);
    }

    public function Store_Permiso(string $nombre, string $tipo, ?int $idRelacion = null, bool $soloCrear = false): Permission
    {
        $data = [
            'name' => $this->cleanHtml($nombre),
            'tipo' => $tipo,
            'guard_name' => 'web',
        ];

        if (!$soloCrear) {
            $permiso = Permission::firstOrCreate(
                ['name' => $data['name'], 'tipo' => $tipo],
                ['id_relacion' => $idRelacion] + $data
            );
        } else {
            $permiso = Permission::create($data);
        }

        $this->SeederRepository->guardarEnSeederPermiso($permiso, $idRelacion);

        return $permiso;
    }

    public function EditarPermiso($request, $permission)
    {

        $permission->update([
            'name' => $request->name,
        ]);

    }
}
