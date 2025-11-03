<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formulario;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;

use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class CamposFormController extends Controller
{
    protected $CatalogoRepository;
    protected $FormularioRepository;

    public function __construct(CatalogoInterface $catalogoInterface, FormularioInterface $formularioInterface)
    {
        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;

    }

    // Mostrar listado y formulario de creación/edición
    public function index(Formulario $formulario)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Campos', 'url' => route('formularios.campos.index', $formulario)],
        ];

        // Cargar todos los campos del formulario, pero sin procesar todas las opciones aún
        $campos = CamposForm::where('form_id', $formulario->id)
            ->orderBy('posicion')
            ->get();

        $categorias = Categoria::all();

        $campos_formulario = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Campos Formulario', true);

        $formularios = Formulario::where('id', '!=', $formulario->id)->get();

        // ⚡ Procesar campos para la vista solo con un límite inicial de opciones
        // Este límite será el número de opciones iniciales que se cargan (ej: 100)
        $limitOpciones = 10;

        $campos = $this->FormularioRepository->CamposFormCat($campos, $limitOpciones);



        return view('formularios.campos.index', compact(
            'breadcrumb',
            'formulario',
            'campos',
            'categorias',
            'campos_formulario',
            'formularios',
            'limitOpciones'
        ));
    }

    public function cargarMasOpciones(CamposForm $campo, Request $request)
    {
        $offset = $request->input('offset');
        $limit = $request->input('limit');

        // Reutilizamos CamposFormCat con un solo campo
        $campoProcesado = $this->FormularioRepository->CamposFormCat(collect([$campo]), $limit, $offset)->first();

        return response()->json($campoProcesado->opciones_catalogo);
    }

    public function buscarOpcion(CamposForm $campo, Request $request)
    {
        $termino = $request->input('termino');

        // Reutilizamos la nueva función que trae todas las opciones sin límite
        $campoProcesado = $this
            ->obtenerOpcionesCompletas(collect([$campo]))
            ->first();

        $opciones = $campoProcesado->opciones_catalogo
            ->filter(fn($item) => stripos($item->catalogo_descripcion, $termino) !== false)
            ->values();

        return response()->json($opciones);
    }
    public function obtenerOpcionesCompletas($campos)
    {
        $resultado = collect();

        foreach ($campos as $campo) {

            if ($campo->categoria_id) {
                // Trae todos los catálogos activos de la categoría
                $campo->opciones_catalogo = $this->CatalogoRepository
                    ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
            } elseif ($campo->form_ref_id) {
                // Campo que referencia a otro formulario
                $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                    ->orderBy('posicion', 'asc')
                    ->first();

                if ($campoReferencia) {
                    $campo->opciones_catalogo = $campo->opcionesFormularioQuery() // Query Builder
                        ->get()
                        ->map(function ($respuesta) use ($campoReferencia) {
                            $valorCampo = $respuesta->camposRespuestas
                                ->firstWhere('cf_id', $campoReferencia->id);

                            return (object) [
                                'catalogo_codigo' => $respuesta->id,
                                'catalogo_descripcion' => $valorCampo->valor ?? 'Sin nombre',
                            ];
                        });
                } else {
                    $campo->opciones_catalogo = collect();
                }
            } else {
                $campo->opciones_catalogo = collect();
            }

            $resultado->push($campo);
        }

        return $resultado;
    }
    // Crear campo
    public function store(Request $request, Formulario $formulario)
    {

        $request->validate([

            'tipo' => 'required',
            'nombre' => [
                'required',
                Rule::unique('campos_forms')->where(function ($query) use ($formulario) {
                    return $query->where('form_id', $formulario->id);
                }),
            ],
            'etiqueta' => 'required',
        ]);

        $posicion = CamposForm::where('form_id', $request->form_id)->max('posicion') + 1;

        $campo = CamposForm::create([
            'form_id' => $formulario->id,
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'posicion' => $posicion,
            'config' => $request->config ?? [],
            'form_ref_id' => $request->formulario_id ?: null,
        ]);

        return redirect()->back()->with('status', 'Campo creado exitosamente.');
    }

    // Actualizar campo
    public function update(Request $request, CamposForm $campo)
    {


        $formulario = Formulario::find($campo->form_id);
        $request->validate([
            'tipo' => 'required',
            'nombre' => [
                'required',
                Rule::unique('campos_forms')->ignore($campo->id)->where(function ($query) use ($formulario) {
                    return $query->where('form_id', $formulario->id);
                }),
            ],
            'etiqueta' => 'required',
        ]);

        $campo->update([
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'config' => $request->config ?? $campo->config,
            'form_ref_id' => $request->formulario_id ?: null,
        ]);
        return redirect()->back()->with('status', 'Campo editado exitosamente.');


    }

    // Eliminar campo
    public function destroy(CamposForm $campo)
    {
        $campo->delete();
        return redirect()->back()->with('status', 'Campo eliminado exitosamente.');


    }

    // Reordenar campos
    public function reordenar(Request $request, Formulario $formulario)
    {
        $orden = $request->orden; // array de IDs en el nuevo orden
        foreach ($orden as $pos => $id) {
            CamposForm::where('id', $id)->update(['posicion' => $pos + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function show(CamposForm $campo)
    {
        // Cargar opciones de catálogo si tiene categoría
        if ($campo->categoria_id) {
            $campo->opciones_catalogo = $this->CatalogoRepository
                ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
        } else {
            $campo->opciones_catalogo = collect();
        }

        return response()->json([
            'success' => true,
            'campo' => $campo
        ]);
    }

    public function checkRespuestas($campoId)
    {
        $campo = CamposForm::findOrFail($campoId);

        // Revisar si el formulario tiene respuestas
        $tieneRespuestas = $campo->formulario()->first()->respuestas()->exists();

        return response()->json([
            'tiene_respuestas' => $tieneRespuestas
        ]);
    }
}
