<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Seccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Interfaces\MenuInterface;
use App\Interfaces\PermisoInterface;
use App\Models\Modulo;
use Spatie\Permission\Models\Permission;
use App\Interfaces\IAInterface;

class MenuController extends Controller
{
    protected $menuRepository;
    protected $PermisoRepository;
    protected $IARepository;

    public function __construct(MenuInterface $MenuInterface, PermisoInterface $permisoInterface, IAInterface $iAInterface)
    {
        $this->PermisoRepository = $permisoInterface;
        $this->menuRepository = $MenuInterface;
        $this->IARepository = $iAInterface;

    }
    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Menus', 'url' => route('menus.index')],
        ];

        $secciones = Seccion::paginate(10);

        $menus = Menu::with('seccion')->paginate(10);

        $modulos = Modulo::where('activo', 1)->get();
        $routes = Route::getRoutes();
        //dd($routes);
        $routes = collect($routes)->filter(function ($route) {
            return str_contains($route->getName(), 'index');
        });

        return view('menus.index', compact('modulos', 'menus', 'secciones', 'routes', 'breadcrumb'));
    }

    public function create()
    {
        $secciones = Seccion::all();  // Obtener todas las secciones
        return view('menus.create', compact('secciones'));
    }

    public function store(Request $request)
    {


        $request->validate([
            'nombre' => 'required|string|max:255',
            'orden' => [
                'required',
                'integer',
                Rule::unique('menus')->where(function ($query) use ($request) {
                    return $query->where('seccion_id', $request->seccion_id);
                }),
            ],
            'seccion_id' => 'required|exists:secciones,id',
            'ruta' => 'required|string|max:255',
        ]);



        $this->menuRepository->CrearMenu($request);

        return redirect()->route('menus.index')->with('status', 'Menú creado exitosamente.');
    }


    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $secciones = Seccion::all();  // Obtener todas las secciones
        return view('menus.edit', compact('menu', 'secciones'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'seccion_id' => 'required|exists:secciones,id',
            'ruta' => 'nullable|string|max:255',
            'orden' => 'nullable|integer',
        ]);

        $menu = Menu::findOrFail($id);
        $menu->update($request->all());

        return redirect()->route('menus.index')->with('status', 'Menú actualizado exitosamente.');
    }


    public function destroy($id)
    {
        $menu = Menu::where('id', $id)->first();

        $permiso = Permission::where('id_relacion', $menu->id)->where('name', $menu->nombre)->first();


        if ($permiso != null) {

            $this->PermisoRepository->eliminarDeSeederPermiso($permiso);
            $permiso->delete();

        }
        if ($menu != null) {

            $this->menuRepository->eliminarDeSeederMenu($menu);
            $menu->delete();
        }
        return redirect()->route('menus.index')->with('status', 'Menú eliminado exitosamente.');
    }


}
