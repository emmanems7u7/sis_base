<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Formulario;
use App\Models\RespuestasForm;


use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;
use App\Exports\ExportPDF;
use Carbon\Carbon;
use App\Exports\ExportExcel;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\FormularioInterface;
use App\Interfaces\PermisoInterface;
use App\Interfaces\CamposFormInterface;
use App\Interfaces\FormConfigInterface;


use App\Models\AuditoriaAccion;
use App\Models\CamposForm;
use App\Models\RespuestasCampo;
use App\Interfaces\CategoriaInterface;
use App\Models\FormConfiguration;

class FormularioController extends Controller
{

    protected $FormularioRepository;
    protected $PermisoRepository;

    protected $CamposFormRepository;
    protected $CatalogoRepository;
    protected $CategoriaRepository;

    protected $formConfigInterface;

    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        PermisoInterface $permisoInterface,
        CategoriaInterface $categoriaInterface,
        FormConfigInterface $formConfigInterface,
        CamposFormInterface $camposFormInterface

    ) {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->PermisoRepository = $permisoInterface;
        $this->PermisoRepository = $permisoInterface;
        $this->CamposFormRepository = $camposFormInterface;
        $this->CategoriaRepository = $categoriaInterface;
        $this->formConfigInterface = $formConfigInterface;


    }
    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
        ];

        $formularios = Formulario::with('estadoCatalogo')->orderBy('created_at', 'desc')->paginate(10);
        return view('formularios.index', compact('formularios', 'breadcrumb'));
    }

    public function create()
    {
        $estado_formularios = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Estado Formulario', true);
        $conf_formularios = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Configuracion Columnas', true);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Crear', 'url' => route('formularios.create')],
        ];
        return view('formularios.create', compact('estado_formularios', 'conf_formularios', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|exists:catalogos,catalogo_codigo',
            'crear_permisos' => 'nullable|in:on',
            'registro_multiple' => 'nullable|in:on',

        ]);

        $formulario = $this->FormularioRepository->CrearFormulario($request);

        if ($request->has('crear_permisos') && $request->input('crear_permisos') == 'on') {
            $this->PermisoRepository->CrearPermisosFormulario($formulario);
        }

        return redirect()->route('formularios.index')->with('status', 'Formulario creado correctamente.');
    }

    public function edit(Formulario $formulario)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Editar', 'url' => route('formularios.edit', $formulario)],
        ];
        $estado_formularios = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Estado Formulario', true);
        $conf_formularios = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Configuracion Columnas', true);


        // Cargar todos los campos del formulario, pero sin procesar todas las opciones
        $campos = $this->CamposFormRepository->GetCampoOrderByPosicion($formulario->id);

        $categorias = $this->CategoriaRepository->GetAll();

        $campos_formulario = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Campos Formulario', true);

        $formularios = Formulario::where('id', '!=', $formulario->id)->get();

        // Procesar campos para la vista solo con un límite inicial de opciones
        $limitOpciones = 10;

        $campos = $this->CamposFormRepository->CamposFormCat($campos, $limitOpciones);


        /*
        $formsRefIds = CamposForm::where('form_id', $formulario->id)
            ->whereNotNull('form_ref_id')
            ->pluck('form_ref_id')
            ->unique()
            ->values();*/

        /*TEMPORAL TRAER TODOS LOS FORMULARIOS PARA ASIGNAR, POSTERIORMENTE EL MODULO DE FORMULARIOS
        SERA COMPLEMENTO DEL MODULO DE MODULOS DINAMICOS Y SE EXTRAERAN LOS FORMULARIOS ASOCIADOS
        A UN MODULO DINAMICO*/

        $formsRefIds = Formulario::all()
            ->pluck('id')
            ->unique()
            ->values();

        $formularios_ref = Formulario::with('campos')->whereIn('id', $formsRefIds)->get();



        // obtener la configuracion general de retorno de mensajes

        $config = FormConfiguration::firstOrCreate(['formulario_id' => $formulario->id], ['config' => []]);

        $fields = $this->formConfigInterface->defaultFields($config);

        return view('formularios.edit', compact(
            'breadcrumb',
            'formulario',
            'campos',
            'categorias',
            'campos_formulario',
            'formularios',
            'limitOpciones',
            'formularios_ref',
            'estado_formularios',
            'conf_formularios',
            'config',
            'fields'
        ));



    }

    public function update(Request $request, Formulario $formulario)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|exists:catalogos,catalogo_codigo',
        ]);

        $config = $formulario->config ?? [];

        if (!($config['crear_permisos'] ?? false) && $request->has('crear_permisos') && $request->crear_permisos === 'on') {
            $this->PermisoRepository->CrearPermisosFormulario($formulario);

        }

        $formulario = $this->FormularioRepository->EditarFormulario($request, $formulario);

        return redirect()->route('formularios.index')->with('status', 'Formulario actualizado correctamente.');
    }

    public function destroy(Formulario $formulario)
    {

        $this->PermisoRepository->EliminarPermisosFormulario($formulario);

        $formulario->delete();


        return redirect()->route('formularios.index')->with('status', 'Formulario eliminado correctamente.');
    }


    public function showCampos($id)
    {
        $formulario = Formulario::with('campos')->findOrFail($id);

        $camposProcesados = $this->CamposFormRepository->CamposFormCat($formulario->campos);

        $formulario->campos = $camposProcesados;

        return response()->json([
            'nombre' => $formulario->nombre,
            'descripcion' => $formulario->descripcion,
            'campos' => $formulario->campos
        ]);

    }


    public function exportPdf(Request $request, Formulario $form)
    {
        $formulario = Formulario::with(
            'campos.opciones_catalogo'
        )->findOrFail($form->id);

        $query = RespuestasForm::where('form_id', $formulario->id)
            ->with('camposRespuestas.campo', 'actor');

        $query = $this->FormularioRepository->aplicarFiltrosFormulario($query, $formulario, $request);

        $respuestas = $query->orderBy('created_at', 'desc')->get();

        $datos = $this->FormularioRepository->generar_informacion_export($respuestas, $formulario);

        $user = auth()->user();

        $fecha = Carbon::now()->format('d-m-Y H:i:s');

        $mpdfConfig = array_merge([
            'margin_top' => 15,
            'margin_right' => 10,
            'margin_bottom' => 30,
            'margin_left' => 10,
        ]);
        return ExportPDF::exportPdf(
            'formularios.export_respuestas',
            [
                'formulario' => $formulario,
                'respuestas' => $datos,
                'export' => $formulario->nombre,
                'user' => $user,
                'fecha' => $fecha,
            ],
            'respuestas_formulario_' . $formulario->id,
            false,
            $mpdfConfig
        );
    }
    public function exportExcel(Formulario $form)
    {

        $formulario = Formulario::with('campos.opciones_catalogo', 'respuestas.camposRespuestas.campo')->findOrFail($form->id);

        $respuestas = $formulario->respuestas()->with('camposRespuestas.campo')->get();

        $datos = $this->FormularioRepository->generar_informacion_export($respuestas, $formulario);

        $user = auth()->user();

        $fecha = Carbon::now()->format('d-m-Y H:i:s');

        $export = new ExportExcel('formularios.export_excel', [
            'formulario' => $formulario,
            'respuestas' => $datos,
            'export' => $formulario->nombre,
            'user' => $user,
            'fecha' => $fecha,

        ], 'formulario_' . $formulario->id);

        return Excel::download($export, $export->getFileName());


    }

    public function obtenerCampos($id)
    {
        $formulario = Formulario::with('campos')->findOrFail($id);

        $camposProcesados = $this->CamposFormRepository->CamposFormCat($formulario->campos);

        return response()->json($camposProcesados->map(function ($campo) {
            return [
                'id' => $campo->id,
                'nombre' => $campo->nombre,
            ];
        }));
    }
    public function obtenerFila($form_id, $respuesta_id)
    {

        $respuesta = RespuestasForm::with(['camposRespuestas.campo'])
            ->where('form_id', $form_id)
            ->where('id', $respuesta_id)
            ->first();


        $datos = $this->GetfilaByFormResp($form_id, $respuesta);

        $formulario = Formulario::find($form_id);

        return response()->json([
            'id' => $respuesta->id,
            'form_id' => $respuesta->form_id,
            'nombre' => $formulario->nombre,
            'datos' => $datos
        ]);
    }
    public function GetfilaByFormResp($form_id, $respuesta)
    {

        if (!$respuesta) {
            return response()->json(['error' => 'No se encontró la respuesta'], 404);
        }

        $datos = [];
        foreach ($respuesta->camposRespuestas as $cr) {
            $datos[$cr->campo->nombre] = $cr->valor;
        }
        return $datos;
    }

    public function obtenerFilaVisor($form_id, $respuesta_id)
    {
        $respuesta = RespuestasForm::with([
            'camposRespuestas',
            'camposRespuestas.campo.opciones_catalogo',
            'formulario.campos',
            'grupos.respuestas.camposRespuestas.campo.opciones_catalogo'
        ])
            ->where('form_id', $form_id)
            ->where('id', $respuesta_id)
            ->first();
        if (!$respuesta) {
            return response()->json(['error' => 'No se encontró la respuesta'], 404);
        }



        $formulario = $respuesta->formulario;

        // Procesar la respuesta principal
        $camposRespuestaPrincipal = $this->FormularioRepository->procesarCamposRespuesta($respuesta, $formulario);

        $asociado = false;


        // Revisar si pertenece a algún grupo
        $grupo = $respuesta->grupos->first(); // tomamos el primer grupo si hay

        $respuestasGrupo = [];

        if ($grupo) {

            foreach ($grupo->respuestas as $resp) {

                if ($resp->id == $respuesta->id) {
                    continue;
                }

                $respuestasGrupo[] = [
                    'respuesta_id' => $resp->id,
                    'campos' => $this->FormularioRepository->procesarCamposRespuesta($resp, $formulario)
                ];
            }
        }





        /* OBTENER REGISTRO ASOCIADO 1:1 CON FORMULARIO*/

        $datos = [];


        foreach ($respuesta->camposRespuestas as $respuestaCampo) {
            $config = $respuestaCampo->campo->config;
            $asociado = isset($config['asociacion']) ?? false;

            if ($asociado) {

                $asociacion = $config['asociacion'];

                $formRefId = $asociacion['form_ref_id'] ?? null;
                $campoRefId = $asociacion['campo_ref_id'] ?? null;
                break;

            }
        }
        if ($asociado) {

            //CAMPF-031 asociado
            $campoAsociado = collect($camposRespuestaPrincipal)->firstWhere('tipo', 'CAMPF-031');


            $valorAsociado = $campoAsociado['valores'][0] ?? null;




            $resp = RespuestasCampo::where('cf_id', $campoRefId)->where('valor', $valorAsociado)->first()->respuesta_id ?? null;


            $respuesta = RespuestasForm::with(['camposRespuestas.campo'])
                ->where('form_id', $formRefId)
                ->where('id', $resp)
                ->first();

            $datos = $this->GetfilaByFormResp($form_id, $respuesta);

        }

        /* OBTENER REGISTRO ASOCIADO 1:1 CON FORMULARIO*/


        $datosRelacionados = [];

        $camposReferenciados = CamposForm::where('form_ref_id', $form_id)->get();

        foreach ($camposReferenciados as $campoRelacionado) {

            $config = $campoRelacionado->config['asociacion'] ?? null;

            if (!$config) {
                continue;
            }

            $campoPadreId = $config['campo_ref_id'] ?? null;

            if (!$campoPadreId) {
                continue;
            }

            // valor del campo padre en la respuesta actual
            $valorPadre = $respuesta->camposRespuestas->firstWhere('cf_id', $campoPadreId)?->valor;

            if (!$valorPadre) {
                continue;
            }

            // respuestas hijas que apuntan a este registro
            $respuestaIds = RespuestasCampo::where('cf_id', $campoRelacionado->id)
                ->where('valor', $valorPadre)
                ->pluck('respuesta_id');

            $respuestasHijas = RespuestasForm::with([
                'camposRespuestas.campo'
            ])
                ->whereIn('id', $respuestaIds)
                ->get();

            foreach ($respuestasHijas as $hija) {

                $datosRelacionados[] = [
                    'formulario_id' => $campoRelacionado->form_id,
                    'respuesta_id' => $hija->id,
                    'campos' => $this->FormularioRepository->procesarCamposRespuesta($hija, $hija->formulario)
                ];
            }
        }
        $datos_asociados_title = configForm($form_id, 'titles.datos_asociados');
        $datos_relacionados_title = configForm($form_id, 'titles.datos_relacionados');
        $grupo_title = configForm($form_id, 'titles.datos_agrupados');
        $detalle_registro = configForm($form_id, 'titles.detalle_registro', null, 'normal', 'none');


        return response()->json([
            'nombre_formulario' => $formulario->nombre,
            'campos' => $camposRespuestaPrincipal,
            'grupo_id' => $grupo->id ?? null,
            'codigo_grupo' => $grupo->codigo ?? null,
            'respuestas_grupo' => $respuestasGrupo,
            'datos_asociados' => $datos,
            'datos_relacionados' => $datosRelacionados,
            'datos_asociados_title' => $datos_asociados_title,
            'datos_relacionados_title' => $datos_relacionados_title,
            'grupo_title' => $grupo_title,
            'detalle_registro' => $detalle_registro,



        ]);
    }
    /**
     * Procesa los campos de una respuesta en el formato listo para la vista
     */


    public function getInfo($formDestinoId)
    {
        $formulario = Formulario::with('campos')->find($formDestinoId);

        if (!$formulario) {
            return response()->json(['error' => 'Formulario no encontrado'], 404);
        }

        $formulario->campos = $this->CamposFormRepository->CamposFormCat($formulario->campos);

        $camposConReferencia = $formulario->campos->filter(fn($campo) => $campo->form_ref_id !== null);
        $formIdsRelacionados = $camposConReferencia->pluck('form_ref_id')->unique();

        $formulariosRelacionados = Formulario::with('campos')
            ->whereIn('id', $formIdsRelacionados)
            ->get();

        return response()->json([
            'formulario' => $formulario,
            'formulariosRelacionados' => $formulariosRelacionados
        ]);
    }

    public function getCampos(Formulario $formulario)
    {
        return response()->json(
            $formulario->campos()
                ->select('id', 'etiqueta', 'tipo')
                ->orderBy('posicion')
                ->get()
        );
    }

    public function detalle($accion_id = null)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
        ];
        if ($accion_id) {
            $acciones = collect([AuditoriaAccion::find($accion_id)]);
        } else {
            $acciones = AuditoriaAccion::orderBy('created_at', 'desc')->paginate(15);
        }

        return view('auditoria.index', compact('acciones', 'breadcrumb'));
    }

    public function guardarConcatenado(Request $request, Formulario $formulario)
    {
        $request->validate([
            'ids' => 'required|array',
            'estructura' => 'required|string',
        ]);

        $ids = $request->input('ids');
        $estructura = $request->input('estructura');

        $config = $formulario->config;

        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        if (!is_array($config)) {
            $config = [];
        }

        $config = array_merge($config, [
            'configuracion_concatenado' => [
                'ids' => $ids,
                'estructura' => $estructura
            ]
        ]);

        $formulario->config = $config;
        $formulario->save();

        return response()->json([
            'success' => true,
            'message' => 'Concatenado guardado correctamente',
            'data' => $config['configuracion_concatenado']
        ]);
    }
    public function guardarAgrupacion(Request $request, Formulario $formulario)
    {
        $request->validate([
            'activa' => 'required|boolean',
            'campo_incremento' => 'nullable|exists:campos_forms,id'
        ]);

        $config = $formulario->config ?? [];

        $config['agrupacion'] = [
            'activa' => $request->activa,
            'campo_incremento' => $request->activa ? $request->campo_incremento : null,
        ];

        $formulario->update([
            'config' => $config
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de agrupación guardada correctamente.'
        ]);
    }


    public function Cambiarfiltros(Request $request)
    {
        $request->validate([
            'formulario_id' => 'required|exists:formularios,id',
            'filtros' => 'nullable|array',
            'filtros.*' => 'in:usuario,fecha'
        ]);

        $formulario = Formulario::find($request->formulario_id);

        $config = $formulario->config ?? [];

        $filtros = $request->input('filtros', []);

        $config['mostrar_usuario'] = in_array('usuario', $filtros);
        $config['mostrar_fecha'] = in_array('fecha', $filtros);

        $formulario->config = $config;
        $formulario->save();

        return response()->json([
            'success' => true,
            'message' => 'Filtros actualizados correctamente.',
            'config' => $config
        ]);
    }

    public function formulariosRelacionados(Formulario $formulario)
    {
        $formularios = CamposForm::where('form_id', $formulario->id)
            ->whereNotNull('form_ref_id')
            ->with('formularioReferencia:id,nombre')
            ->get()
            ->pluck('formularioReferencia')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'nombre' => $f->nombre
                ];
            });

        return response()->json($formularios);
    }
}
