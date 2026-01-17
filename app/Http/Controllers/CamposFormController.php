<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formulario;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\CamposFormInterface;

use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class CamposFormController extends Controller
{
    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $CamposFormRepository;

    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        CamposFormInterface $CamposFormInterface,

    ) {
        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->CamposFormRepository = $CamposFormInterface;


    }


    public function index(Formulario $formulario)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Campos', 'url' => route('formularios.campos.index', $formulario)],
        ];

        // Cargar todos los campos del formulario, pero sin procesar todas las opciones
        $campos = CamposForm::where('form_id', $formulario->id)
            ->orderBy('posicion')
            ->get();

        $categorias = Categoria::all();

        $campos_formulario = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Campos Formulario', true);

        $formularios = Formulario::where('id', '!=', $formulario->id)->get();

        // Procesar campos para la vista solo con un límite inicial de opciones
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

        $campoProcesado = $this->FormularioRepository->CamposFormCat(collect([$campo]), $limit, $offset)->first();

        return response()->json($campoProcesado->opciones_catalogo);
    }

    public function buscarOpcion(CamposForm $campo, Request $request)
    {
        $termino = $request->input('termino');

        $campoProcesado = $this->CamposFormRepository->obtenerOpcionesCompletas(collect([$campo]))
            ->first();

        $opciones = $campoProcesado->opciones_catalogo
            ->filter(fn($item) => stripos($item->catalogo_descripcion, $termino) !== false)
            ->values();

        return response()->json($opciones);
    }

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


        $campo = $this->CamposFormRepository->CrearCampoForm($request, $formulario);


        return redirect()->back()->with('status', 'Campo creado exitosamente.');
    }


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


        $campo = $this->CamposFormRepository->EditarCampoForm($request, $campo);



        return redirect()->back()->with('status', 'Campo editado exitosamente.');


    }

    public function destroy(CamposForm $campo)
    {
        $campo->delete();

        return redirect()->back()->with('status', 'Campo eliminado exitosamente.');


    }

    public function reordenar(Request $request, Formulario $formulario)
    {
        $orden = $request->orden;
        foreach ($orden as $pos => $id) {
            CamposForm::where('id', $id)->update(['posicion' => $pos + 1]);
        }
        return response()->json(['success' => true]);
    }

    public function show(CamposForm $campo)
    {
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

        $tieneRespuestas = $campo->formulario()->first()->respuestas()->exists();

        return response()->json([
            'tiene_respuestas' => $tieneRespuestas
        ]);
    }
}
