<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Interfaces\RoleInterface;
use App\Interfaces\PermisoInterface;


class PermissionController extends Controller
{


    protected $PermisoRepository;
    public function __construct(PermisoInterface $PermisoInterface)
    {

        $this->PermisoRepository = $PermisoInterface;
    }
    public function index(request $request)
    {
        $search = $request->input('search');
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Permisos', 'url' => route('permissions.index')],
        ];
        $permissions = Permission::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        })->paginate(30);

        $permisos = $this->PermisoRepository->GetPermisosTipo('permiso');

        $cat_permisos = $permisos->map(function ($permiso) {
            return explode('.', $permiso->name)[0];
        })->unique()->values();

        return view('permisos.index', compact('permissions', 'cat_permisos', 'breadcrumb', 'search'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        $this->PermisoRepository->CrearPermiso($request);
        return redirect()->back()->with('success', 'Rol creado correctamente.');
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
