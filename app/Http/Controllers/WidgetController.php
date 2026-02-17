<?php

namespace App\Http\Controllers;

use App\Models\Widget;
use Illuminate\Http\Request;
use App\Models\Catalogo;
use App\Interfaces\CatalogoInterface;
use App\Models\Modulo;

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
        $modulos = Modulo::all();
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Widgets', 'url' => route('widgets.index')],
            ['name' => 'Crear Widget', 'url' => ''],
        ];

        return view('widgets.create', compact('modulos', 'catalogos', 'breadcrumb'));
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
                $configuracion = [
                    'texto' => $request->configuracion['texto'] ?? 'Botón',
                    'color' => $request->configuracion['color'] ?? '#0d6efd',
                    'url' => route('formularios.registrar', ['form' => $request->formulario_id, 'modulo' => 0]),
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
            case 'WID-010':
                break;

        }

        $widget = Widget::create([
            'modulo_id' => $request->modulo_id,
            'formulario_id' => $request->formulario_id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'configuracion' => $configuracion,
        ]);



        return redirect()->route('widgets.index')->with('status', 'Widget creado correctamente.');
    }


    public function edit(Widget $widget)
    {
        $modulos = Modulo::where('activo', 1)->get();

        $catalogos = Catalogo::where('catalogo_tipo', 'TIPO_WIDGET')
            ->where('activo', 1)
            ->get();

        return view('widgets.edit', compact(
            'widget',
            'modulos',
            'catalogos'
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
            'modulo_id' => $request->modulo_id,
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
