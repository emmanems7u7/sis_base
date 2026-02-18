<?php
namespace App\Repositories;

use App\Interfaces\PermisoInterface;
use Spatie\Permission\Models\Role;

use App\Models\Menu;
use App\Models\Seccion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


use App\Interfaces\SeederInterface;
use App\Models\Categoria;
use App\Models\Permission;
use App\Interfaces\CatalogoInterface;

class PermisoRepository extends BaseRepository implements PermisoInterface
{
    protected $permissions;
    protected $SeederRepository;

    protected $CatalogoRepository;



    public function __construct(SeederInterface $seederRepository, CatalogoInterface $catalogoInterface)
    {
        parent::__construct();
        $this->permissions = Permission::all();
        $this->SeederRepository = $seederRepository;
        $this->CatalogoRepository = $catalogoInterface;
    }
    public function GetPermisos($role = null)
    {
        $permisos = Permission::whereNull('id_relacion')
            ->where('tipo', 'permiso')
            ->get();

        return $permisos->map(function ($permiso) use ($role) {
            $permiso->checked = $role ? $role->hasPermissionTo($permiso) : false;

            $permiso->nombreParaVistaStr = $permiso->nombreParaVista();

            return $permiso;
        });
    }
    public function GetPermisosTipo($tipo)
    {

        return $this->permissions->where('tipo', $tipo);
    }
    public function GetPermisosMenu($role = null)
    {
        $permisosSeccion = Permission::where('tipo', 'seccion')->get();
        $permisosMenu = Permission::where('tipo', 'menu')->get();
        $secciones = Seccion::with('menus')->get();

        return $permisosSeccion->map(function ($permisoSeccion) use ($permisosMenu, $secciones, $role) {

            $seccion = $secciones->firstWhere('id', $permisoSeccion->id_relacion);

            if (!$seccion) {
                $permisoSeccion->menus = collect();
                $permisoSeccion->checked = false;
                return $permisoSeccion;
            }

            // Mapear menús de la sección a permisos de tipo 'menu'
            $menus = $seccion->menus->map(function ($menu) use ($permisosMenu, $role) {
                $permisoMenu = $permisosMenu->firstWhere('id_relacion', $menu->id);
                if ($permisoMenu) {
                    $permisoMenu->checked = $role ? $role->hasPermissionTo($permisoMenu) : false;
                    return $permisoMenu;
                }
                return null;
            })->filter(); // elimina nulls

            $permisoSeccion->checked = $role ? $role->hasPermissionTo($permisoSeccion) : false;
            $permisoSeccion->menus = $menus;

            return $permisoSeccion;
        });
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
        $this->Store_Permiso(
            $request->name,
            'permiso',
            null,
            true
        );
    }

    public function Store_Permiso(
        string $nombre = null,
        string $tipo,
        ?int $idRelacion = null,
        bool $soloCrear = false
    ): Permission {

        $data = [
            'name' => $this->cleanHtml($nombre),
            'tipo' => $tipo,
            'guard_name' => 'web',
            'dinamico' => 0,

        ];

        if (!$soloCrear) {

            $permiso = Permission::firstOrCreate(
                [
                    'name' => $data['name'],
                    'tipo' => $tipo
                ],

                ['id_relacion' => $idRelacion] + $data
            );
            $this->SeederRepository->guardarEnSeederPermiso($permiso, $idRelacion);
            return $permiso;

        } else {

            if ($nombre == null) {
                $dinamico = 1;

            } else {

                $dinamico = 0;
            }

            $permisosRequest = request()->input('permisos', []);
            $ultimoPermiso = null;

            foreach ($permisosRequest as $permisoStr) {

                $ultimoPermiso = Permission::create(
                    [
                        'name' => $this->cleanHtml($permisoStr),
                        'tipo' => $tipo,
                        'guard_name' => 'web',
                        'id_relacion' => null,
                        'dinamico' => $dinamico,

                    ]
                );

                $this->SeederRepository->guardarEnSeederPermiso($ultimoPermiso, null);
            }

            return $ultimoPermiso;
        }


    }


    public function CrearPermisosFormulario($formulario)
    {

        // Obtener la categoría de permisos
        $categoria = Categoria::where('nombre', 'Tipos de permisos para roles')->firstOrFail();

        // Obtener los permisos del catálogo
        $catalogo_permisos = $this->CatalogoRepository
            ->obtenerCatalogosPorCategoriaID($categoria->id, true);

        // Recorrer cada permiso del catálogo y crear permiso dinámico
        $ultimoPermiso = null;

        foreach ($catalogo_permisos as $permisoC) {
            $permisoStr = $formulario->id . '.' . $permisoC->catalogo_descripcion;

            $ultimoPermiso = Permission::create([
                'name' => $permisoStr,
                'tipo' => 'permiso',
                'guard_name' => 'web',
                'id_relacion' => null,
                'dinamico' => 1,
            ]);

            // Guardar en seeder
            $this->SeederRepository->guardarEnSeederPermiso($ultimoPermiso, null);
        }


    }

    public function EliminarPermisosFormulario($formulario)
    {
        // Obtener categoría
        $categoria = Categoria::where('nombre', 'Tipos de permisos para roles')->firstOrFail();

        // Obtener catálogo
        $catalogo_permisos = $this->CatalogoRepository
            ->obtenerCatalogosPorCategoriaID($categoria->id, true);

        foreach ($catalogo_permisos as $permisoC) {

            $permisoStr = $formulario->id . '.' . $permisoC->catalogo_descripcion;

            $permiso = Permission::where('name', $permisoStr)
                ->where('dinamico', 1)
                ->first();

            if ($permiso) {

                //Eliminar del seeder
                $this->SeederRepository->eliminarDeSeederPermiso($permiso);

                // Eliminar relaciones con roles
                $permiso->roles()->detach();

                $permiso->delete();
            }
        }
    }

    public function EditarPermiso($request, $permission)
    {

        $permission->update([
            'name' => $request->name,
        ]);

    }
    public function eliminarDeSeederPermiso($permiso)
    {
        $this->SeederRepository->eliminarDeSeederPermiso($permiso);
    }

}
