<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Formulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
class ModuloController extends Controller
{
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

        return response()->json(['exists' => false]);
    }
    public function ModulosIndex(Request $request, $modulo_id)
    {
        $agent = new Agent();
        $isMobile = $agent->isMobile();


        $modulo = Modulo::with('formularios.campos')->findOrFail($modulo_id);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Módulo ' . $modulo->nombre, 'url' => route('modulos.index')],
        ];


        $search = $request->input('search');

        // Preparar un array para pasar a la vista
        $formulariosConRespuestas = [];

        foreach ($modulo->formularios as $formulario) {
            $query = $formulario->respuestas()->with('camposRespuestas.campo', 'actor');

            // Filtro de búsqueda por actor
            if (!empty($search)) {
                $query->whereHas('actor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $respuestas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

            $formulariosConRespuestas[] = [
                'formulario' => $formulario,
                'respuestas' => $respuestas,
            ];
        }



        return view('modulosDinamicos.index', compact('isMobile', 'formulariosConRespuestas', 'modulo', 'breadcrumb'));
    }
}
