<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Formulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use App\Interfaces\CatalogoInterface;
use App\Models\FormLogicRule;
use App\Interfaces\FormularioInterface;
use App\Models\RespuestasForm;

class ModuloController extends Controller
{

    protected $CatalogoRepository;
    protected $isMobile;
    protected $agent;
    protected $FormularioRepository;

    public function __construct(CatalogoInterface $catalogoInterface, FormularioInterface $formularioInterface)
    {
        $this->agent = new Agent();
        $this->isMobile = $this->agent->isMobile();
        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;


    }
    public function index(Request $request)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulos', 'url' => route('modulos.index')],
        ];

        // Obtener el término de búsqueda
        $search = $request->input('search');

        // Consulta con búsqueda y paginación
        $modulos = Modulo::with('padre')
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(10);


        $modulos->appends(['search' => $search]);

        return view('modulos.index', compact('modulos', 'breadcrumb', 'search'));
    }
    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulos', 'url' => route('modulos.index')],
            ['name' => 'Crear', 'url' => route('modulos.create')],
        ];

        $modulosPadre = Modulo::whereNull('modulo_padre_id')->get();
        $formularios = Formulario::all();

        return view('modulos.create', compact('modulosPadre', 'formularios', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Convertir a minúsculas para evitar bypass con mayúsculas
                    $valor = strtolower($value);

                    // Prohibir <script> y javascript: 
                    if (str_contains($valor, '<script') || str_contains($valor, 'javascript:')) {
                        $fail('La descripción no puede contener código JavaScript.');
                    }
                }
            ],
            'modulo_padre_id' => 'nullable|exists:modulos,id',
            'formularios' => 'nullable|array',
            'formularios.*' => 'exists:formularios,id',
        ]);
        // Crear slug manualmente
        $slug = Str::slug($data['nombre']);

        $modulo = Modulo::create([
            'nombre' => $data['nombre'],
            'slug' => $slug,
            'descripcion' => $data['descripcion'] ?? null,
            'modulo_padre_id' => $data['modulo_padre_id'] ?? null,
            'activo' => true,
        ]);

        if (!empty($data['formularios'])) {
            $modulo->formularios()->sync($data['formularios']);
        }

        return redirect()->route('modulos.index')->with('status', 'Módulo creado correctamente.');
    }

    public function edit(Modulo $modulo)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulos', 'url' => route('modulos.index')],
            ['name' => 'Editar', 'url' => route('modulos.edit', $modulo->id)],
        ];

        $modulosPadre = Modulo::whereNull('modulo_padre_id')
            ->where('id', '!=', $modulo->id)
            ->get();

        $formularios = Formulario::all();
        $formulariosSeleccionados = $modulo->formularios->pluck('id')->toArray();

        return view('modulos.edit', compact(
            'modulo',
            'modulosPadre',
            'formularios',
            'formulariosSeleccionados',
            'breadcrumb'
        ));
    }

    public function update(Request $request, Modulo $modulo)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    // Convertir a minúsculas para evitar bypass con mayúsculas
                    $valor = strtolower($value);

                    // Prohibir <script> y javascript: 
                    if (str_contains($valor, '<script') || str_contains($valor, 'javascript:')) {
                        $fail('La descripción no puede contener código JavaScript.');
                    }
                }
            ],
            'modulo_padre_id' => 'nullable|exists:modulos,id',
            'formularios' => 'nullable|array',
            'formularios.*' => 'exists:formularios,id',
        ]);

        $data['slug'] = Str::slug($data['nombre']);
        $modulo->update($data);
        $modulo->formularios()->sync($data['formularios'] ?? []);

        return redirect()->route('modulos.index')->with('status', 'Módulo actualizado correctamente.');
    }

    public function destroy(Modulo $modulo)
    {
        $modulo->delete();
        return redirect()->route('modulos.index')->with('status', 'Módulo eliminado correctamente.');
    }


    public function checkFormulario($formulario_id)
    {
        // Buscamos si ya existe en algún módulo
        $moduloAsociado = DB::table('formulario_modulo')
            ->join('modulos', 'modulos.id', '=', 'formulario_modulo.modulo_id')
            ->where('formulario_modulo.formulario_id', $formulario_id)
            ->select('modulos.id', 'modulos.nombre')
            ->first();

        if ($moduloAsociado) {
            return response()->json([
                'exists' => true,
                'modulo' => $moduloAsociado->nombre
            ]);
        }
        DB::disconnect();
        return response()->json(['exists' => false]);
    }
    public function ModulosIndex(Request $request, $modulo_id)
    {

        $agent = new Agent();
        $isMobile = $agent->isMobile();

        $modulo = Modulo::with([
            'formularios' => fn($q) => $q->wherePivot('activo', true)->with('campos')
        ])->findOrFail($modulo_id);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulo ' . $modulo->nombre, 'url' => route('modulos.index')],
        ];

        $formulariosConRespuestas = [];

        foreach ($modulo->formularios as $formulario) {
            $formulariosConRespuestas[] = $this->FormularioRepository->procesarFormularioConFiltros(
                $formulario,
                $request,
                'page_' . $formulario->id // paginación independiente
            );
        }

        $formularios_asociados = Modulo::with('formularios')->findOrFail($modulo_id);


        $config = $modulo->configuracion ?? [];
        $modo = $config['modo'] ?? 'mostrar_todos';


        return view(
            'modulosDinamicos.index',
            compact('isMobile', 'formulariosConRespuestas', 'modulo', 'breadcrumb', 'formularios_asociados', 'modo')
        );
    }

    public function ModuloAdmin($modulo)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulos', 'url' => route('modulos.index')],
            ['name' => 'Administrar Modulo', 'url' => route('modulos.index')],

        ];


        $modulo = Modulo::with('formularios.campos')->find($modulo);



        // Obtenemos los IDs de los formularios asociados
        $formIds = $modulo->formularios->pluck('id');


        // Ahora filtramos las reglas solo de esos formularios
        $rules = FormLogicRule::with(['formulario', 'actions.formularioDestino'])
            ->whereIn('form_id', $formIds)
            ->get();

        $isMobile = $this->isMobile;

        $rules->operacion = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);


        // Grafo final
        $grafo = [
            'formularios' => [],
            'relaciones' => [],
        ];

        foreach ($modulo->formularios as $formulario) {

            // Nodo formulario + sus campos
            $grafo['formularios'][$formulario->id] = [
                'id' => $formulario->id,
                'nombre' => $formulario->nombre,
                // Convertir la collection a array plano
                'campos' => $formulario->campos->map(function ($campo) {
                    return [
                        'id' => $campo->id,
                        'nombre' => $campo->nombre,
                        'tipo' => $campo->tipo,
                        'form_ref_id' => $campo->form_ref_id,
                    ];
                })->values()->all(), // <-- aquí usamos all() para obtener array plano
            ];

            // Relaciones (solo campos con referencia)
            foreach ($formulario->campos as $campo) {
                if ($campo->form_ref_id) {
                    $grafo['relaciones'][] = [
                        'from' => $formulario->id,
                        'to' => $campo->form_ref_id,
                        'campo' => $campo->nombre,
                        'tipo' => $campo->tipo,
                    ];
                }
            }
        }

        return view('modulos.administrar', compact('isMobile', 'rules', 'modulo', 'breadcrumb', 'grafo'));

    }

    public function toggle(Request $request)
    {
        $request->validate([
            'modulo_id' => 'required|exists:modulos,id',
            'formulario_id' => 'required|exists:formularios,id',
            'activo' => 'required|boolean',
        ]);

        $modulo = Modulo::findOrFail($request->modulo_id);

        // Actualizar SOLO el campo activo del pivot
        $modulo->formularios()->updateExistingPivot(
            $request->formulario_id,
            ['activo' => $request->activo]
        );

        return response()->json([
            'success' => true,
            'mensaje' => $request->activo
                ? 'Formulario activado correctamente'
                : 'Formulario desactivado correctamente'
        ]);
    }

    public function actualizarConfiguracion(Request $request, Modulo $modulo)
    {
        // Validar que recibimos un JSON válido
        $data = $request->validate([
            'configuracion' => 'required|array',
        ]);

        $modulo->configuracion = $data['configuracion'];
        $modulo->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Configuración actualizada correctamente',
            'configuracion' => $modulo->configuracion,
        ]);
    }
    public function GetFormularios(Modulo $modulo)
    {
        return response()->json(
            $modulo->formularios()
                ->wherePivot('activo', 1)
                ->select('formularios.id', 'formularios.nombre')
                ->get()
        );
    }
}
