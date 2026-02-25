<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formulario;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\CamposFormInterface;

use App\Models\Categoria;
use App\Models\RespuestasCampo;
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

        $formsRefIds = CamposForm::where('form_id', $formulario->id)
            ->whereNotNull('form_ref_id')
            ->pluck('form_ref_id')
            ->unique()
            ->values();

        $formularios_ref = Formulario::with('campos')->whereIn('id', $formsRefIds)->get();
        return view('formularios.campos.index', compact(
            'breadcrumb',
            'formulario',
            'campos',
            'categorias',
            'campos_formulario',
            'formularios',
            'limitOpciones',
            'formularios_ref'
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

    /*

    public function buscarOpcion(CamposForm $campo, Request $request)
    {
        $termino = $request->input('termino');

        $campoProcesado = $this->CamposFormRepository->obtenerOpcionesCompletas(collect([$campo]))
            ->first();

        $opciones = $campoProcesado->opciones_catalogo
            ->filter(fn($item) => stripos($item->catalogo_descripcion, $termino) !== false)
            ->first();


        $respuesta = $this->BuscaRespuesta($campo, valor: $opciones->catalogo_codigo);



        return response()->json([
            'catalogo_codigo' => $opciones->catalogo_codigo,
            'catalogo_descripcion' => $opciones->catalogo_descripcion,
            'campo_referencia' => $respuesta['campo_referencia'],
            'valor' => $respuesta['valor'],

        ]);
    }*/

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

    public function toggleVisible(Request $request)
    {
        $campo = CamposForm::findOrFail($request->campo_id);

        $config = $campo->config ?? [];

        $config['visible_listado'] = (bool) $request->visible_listado;

        $campo->config = $config;
        $campo->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente'
        ]);
    }

    public function GuardarAutocompletado(Request $request)
    {
        $campo = CamposForm::find($request->campo_id);

        $campo->config = array_merge(
            $campo->config ?? [],
            ['autocompletar' => $request->valor]
        );

        $campo->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
            'valor' => $campo->config['autocompletar']
        ]);

    }

    public function guardarRelacion(Request $request)
    {
        $request->validate([
            'campo_principal_id' => 'required|exists:campos_forms,id',
            'form_ref_id' => 'required|exists:formularios,id',
            'campo_ref_id' => 'required|exists:campos_forms,id',
        ]);

        $campo = CamposForm::findOrFail($request->campo_principal_id);

        // Si tienes cast a array en el modelo
        $config = $campo->config ?? [];

        if (is_string($config)) {
            $config = json_decode($config, true) ?? [];
        }

        // Guardar relación dentro de config
        $config['relacion'] = [

            'form_ref_id' => $request->form_ref_id,
            'campo_ref_id' => $request->campo_ref_id,
        ];

        $campo->config = $config;

        // También puedes guardar directamente en la columna
        $campo->form_ref_id = $request->form_ref_id;

        $campo->save();

        return response()->json([
            'success' => true,
            'message' => 'Relación guardada correctamente'
        ]);
    }

    public function obtenerData(Request $request)
    {
        $request->validate([
            'campo_id' => 'required|exists:campos_forms,id',
            'valor' => 'required'
        ]);


        $campo = CamposForm::findOrFail($request->campo_id);


        $respuesta = $this->BuscaRespuesta($campo, $request->valor);

        return response()->json(
            $respuesta
        );

    }

    function BuscaRespuesta($campo, $valor)
    {
        $campo_referencia = CamposForm::where('form_id', $campo->form_id)
            ->where('config->relacion->form_ref_id', $campo->form_ref_id)
            ->first();


        if (!$campo_referencia) {
            return response()->json(['success' => false]);
        }


        $campoRefId = $campo_referencia->config['relacion']['campo_ref_id'] ?? null;


        $respuesta = RespuestasCampo::where('respuesta_id', $valor)
            ->where('cf_id', $campoRefId)
            ->first();

        return [
            'success' => true,
            'relacion' => $campo_referencia->config['relacion'],
            'campo_referencia' => $campo_referencia->id,
            'valor' => $respuesta->valor,

        ];
    }

}
