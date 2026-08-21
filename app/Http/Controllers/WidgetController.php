<?php

namespace App\Http\Controllers;

use App\Models\Widget;
use Illuminate\Http\Request;
use App\Models\Formulario;
use App\Interfaces\CatalogoInterface;
use App\Models\Modulo;
use Illuminate\Support\Facades\Route;

class WidgetController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $CatalogoInterface)
    {

        $this->CatalogoRepository = $CatalogoInterface;
    }
    public function index()
    {
        $widgets = Widget::orderBy('id', 'desc')->get();

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Widgets', 'url' => ''],
        ];

        return view('widgets.index', compact('widgets', 'breadcrumb'));
    }

    // Mostrar el formulario de creación
    public function create()
    {
        $catalogos = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Tipos de Widget', true);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Widgets', 'url' => route('widgets.index')],
            ['name' => 'Crear Widget', 'url' => ''],
        ];

        $routes = Route::getRoutes();
        //dd($routes);
        $routes = collect($routes)
            ->filter(function ($route) {
                return str_contains($route->getName(), 'index');
            })
            ->map(function ($route) {
                return [
                    'nombre' => $route->getName(),
                    'tipo' => 'ruta',
                ];
            })
            ->values();

        $routes->push([
            'nombre' => '__crear_modulo_formulario__',
            'label' => 'Crear módulo / formulario asociado',
            'tipo' => 'accion',
        ]);
        $routes->push([
            'nombre' => '__ver_modulo_formulario__',
            'label' => 'Ver módulo / formulario asociado',
            'tipo' => 'accion',
        ]);

        $modulos = Modulo::where('activo', 1)
            ->with([
                'formularios' => function ($query) {
                    $query->wherePivot('activo', 1);
                }
            ])
            ->get();

        return view('widgets.create', compact('modulos', 'catalogos', 'breadcrumb', 'routes'));
    }

    // Guardar widget
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string',
            'descripcion' => 'nullable|string',
        ]);
        $configuracion = [];

        switch ($request->tipo) {

            case 'WID-001':


                //__crear_modulo_formulario__ 
                switch ($request->configuracion['url']) {
                    case '__crear_modulo_formulario__':

                        $url = route('formularios.registrar', ['form' => $request->formulario_id, 'modulo' => $request->modulo_id]);
                        break;
                    case '__ver_modulo_formulario__':

                        $url = route('modulo.index', $request->modulo_id);
                        break;
                    default:
                        $url = route($request->configuracion['url']);
                        break;
                }

                $configuracion = [
                    'texto' => $request->configuracion['texto'] ?? 'Botón',
                    'color' => $request->configuracion['color'] ?? '#0d6efd',
                    'url' => $url,
                    'valor' => $request->configuracion['valor'] ?? 0,
                    'icono' => $request->configuracion['icono'] ?? null,
                ];
                break;

            case 'WID-002':
                $configuracion = [
                    'campo_id' => $request->configuracion['campo_id'] ?? null,

                    'tipo_estadistica' => $request->configuracion['tipo_estadistica'] ?? 'total',

                    'filtros' => [
                        'campo' => [
                            'cf_id' => $request->configuracion['filtros']['campo']['cf_id'] ?? null,
                            'valor' => $request->configuracion['filtros']['campo']['valor'] ?? null,
                        ],

                        'fecha' => $request->configuracion['filtros']['fecha'] ?? 'mes_actual',
                    ],
                ];
                break;
            case 'WID-003':
                break;
            case 'WID-004':
                break;
            case 'WID-005':
                break;
            case 'WID-006':
                break;
            case 'WID-007':
            case 'WID-008':
            case 'WID-009':
                $configuracion = $request->configuracion ?? [];
                break;

            case 'WID-010':

                $configuracion = $request->configuracion ?? [];
                $configuracion['mostrar_icono'] = isset($request->configuracion['mostrar_icono']);
                $configuracion['mostrar_descripcion'] = isset($request->configuracion['mostrar_descripcion']);

                break;

        }

        $widget = Widget::create([
            'formulario_id' => $request->formulario_id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'configuracion' => $configuracion,
        ]);



        return redirect()->route('widgets.index')->with('status', 'Widget creado correctamente.');
    }


    public function edit(Widget $widget)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Widgets', 'url' => route('widgets.index')],
            ['name' => 'Editar Widget', 'url' => ''],
        ];

        $formularios = Formulario::where('estado', 'EFORM-002')->get();

        $catalogos = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Tipos de Widget', true);

        return view('widgets.edit', compact(
            'widget',
            'formularios',
            'catalogos',
            'breadcrumb'
        ));
    }

    public function update(Request $request, Widget $widget)
    {
        $request->validate([
            'nombre' => 'required',
            'tipo' => 'required',
            'modulo_id' => 'nullable|exists:modulos,id',
            'formulario_id' => 'nullable|exists:formularios,id',
        ]);

        $widget->update([
            'formulario_id' => $request->formulario_id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()
            ->route('widgets.index')
            ->with('status', 'Widget actualizado correctamente');
    }
    public function destroy(Widget $widget)
    {
        $widget->delete();

        return redirect()
            ->route('widgets.index')
            ->with('status', 'Widget eliminado correctamente');
    }
}
