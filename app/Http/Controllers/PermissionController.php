<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Interfaces\RoleInterface;
use App\Interfaces\PermisoInterface;
use App\Models\Formulario;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\CatalogoInterface;
use App\Models\Categoria;

class PermissionController extends Controller
{

    protected $CatalogoRepository;

    protected $PermisoRepository;
    public function __construct(
        PermisoInterface $PermisoInterface,
        CatalogoInterface $catalogoInterface
    ) {
        $this->CatalogoRepository = $catalogoInterface;
        $this->PermisoRepository = $PermisoInterface;
    }
    public function index(request $request)
    {
        $search = $request->input('search');
        $search2 = $request->input('search2');

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Permisos', 'url' => route('permissions.index')],
        ];

        $permissions = Permission::whereNull('id_relacion')
            ->where(function ($query) {
                $query->where('dinamico', 0)
                    ->orWhereNull('dinamico');
            })
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(30);

        $permisos = $this->PermisoRepository->GetPermisosTipo('permiso')
            ->filter(function ($permiso) {
                return is_null($permiso->id_relacion) && ($permiso->dinamico === 0 || is_null($permiso->dinamico));
            });

        $cat_permisos = $permisos->map(function ($permiso) {
            return explode('.', $permiso->name)[0];
        })->unique()->values();


        // -----------------------------
        // Permisos dinámicos = 1
        // -----------------------------
        $permissionsDinamic = Permission::whereNull('id_relacion')
            ->where('dinamico', 1)
            ->when($search2, function ($query, $search2) {
                $query->where('name', 'like', "%{$search2}%");
            })
            ->paginate(30);

        // Obtener todos los permisos tipo 'permiso'
        $permisos2 = $this->PermisoRepository->GetPermisosTipo('permiso')
            ->filter(function ($permiso) {
                return $permiso->dinamico === 1 && is_null($permiso->id_relacion);
            })
            ->map(function ($permiso) {
                return $permiso->nombreParaVista();
            })
            ->unique()
            ->values();

        $cat_permisosD = $permisos2->map(function ($permiso) {
            return explode('.', $permiso)[0];
        })
            ->unique()
            ->values();


        $formularios = Formulario::get();

        $categoria = Categoria::where('nombre', 'Tipos de permisos para roles')->first();

        $catalogo_permisos = $this->CatalogoRepository
            ->obtenerCatalogosPorCategoriaID($categoria->id, true);

        return view('permisos.index', compact('catalogo_permisos', 'cat_permisosD', 'permissionsDinamic', 'formularios', 'permissions', 'cat_permisos', 'breadcrumb', 'search'));
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'permisos' => ['required', 'array', 'min:1'],
            'permisos.*' => ['required'],
            'name' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {


            $categoria = Categoria::where('nombre', 'Tipos de permisos para roles')->first();

            $catalogo_permisos = $this->CatalogoRepository
                ->obtenerCatalogosPorCategoriaID($categoria->id, true);

            $accionesPermitidas = $catalogo_permisos->pluck('catalogo_descripcion')->toArray();

            $nombre = $request->input('name');

            foreach ($request->input('permisos', []) as $permisoStr) {
                $parts = explode('.', $permisoStr);

                if (count($parts) !== 2) {
                    $validator->errors()->add('permisos', "El formato de '$permisoStr' es inválido.");
                    continue;
                }

                [$base, $accion] = $parts;

                // Verificar acción permitida
                if (!in_array($accion, $accionesPermitidas)) {
                    $validator->errors()->add('permisos', "La acción '$accion' no es válida.");
                }

                // Si name está vacío → es formulario, base debe ser ID válido y existir
                if (empty($nombre)) {
                    if (!is_numeric($base)) {
                        $validator->errors()->add('permisos', "El ID del formulario en '$permisoStr' no es válido.");
                    } else {
                        if (!Formulario::where('id', $base)->exists()) {
                            $validator->errors()->add('permisos', "El formulario con ID $base no existe.");
                        }
                    }
                }

                // Si name tiene contenido → es un permiso "manual", no validar formulario
                // Solo validar duplicado
                if (Permission::where('name', $permisoStr)->exists()) {
                    $validator->errors()->add('permisos', "El permiso '$permisoStr' ya existe.");
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->PermisoRepository->CrearPermiso($request);
        return redirect()->back()->with('status', 'Permisos creados correctamente.');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json(['status' => 'success', 'permission' => $permission]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $this->PermisoRepository->EditarPermiso($request, $permission);

        return redirect()->back()->with('success', 'Permiso Actualizado Correctamente');

    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['status' => 'success']);
    }
}
