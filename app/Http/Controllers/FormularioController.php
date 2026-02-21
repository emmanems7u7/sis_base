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

use App\Models\AuditoriaAccion;

use function Laravel\Prompts\form;

class FormularioController extends Controller
{

    protected $FormularioRepository;
    protected $PermisoRepository;

    protected $CatalogoRepository;
    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        PermisoInterface $permisoInterface
    ) {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->PermisoRepository = $permisoInterface;

    }
    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
        ];

        $formularios = Formulario::orderBy('created_at', 'desc')->paginate(10);
        return view('formularios.index', compact('formularios', 'breadcrumb'));
    }

    public function create()
    {
        $estado_formularios = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Estado Formulario', true);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Crear', 'url' => route('formularios.create')],
        ];
        return view('formularios.create', compact('estado_formularios', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|exists:catalogos,catalogo_codigo',
            'crear_permisos' => 'nullable|in:on',
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

        return view('formularios.edit', compact('estado_formularios', 'formulario', 'breadcrumb'));
    }

    public function update(Request $request, Formulario $formulario)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado' => 'required|string|exists:catalogos,catalogo_codigo',
        ]);
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

        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);

        $formulario->campos = $camposProcesados;

        return response()->json([
            'nombre' => $formulario->nombre,
            'descripcion' => $formulario->descripcion,
            'campos' => $formulario->campos
        ]);

    }


    public function exportPdf(Formulario $form)
    {
        $formulario = Formulario::with('campos.opciones_catalogo', 'respuestas.camposRespuestas.campo')->findOrFail($form->id);

        $respuestas = $formulario->respuestas()->with('camposRespuestas.campo')->get();

        $datos = $this->FormularioRepository->generar_informacion_export($respuestas, $formulario);

        $user = auth()->user();

        $fecha = Carbon::now()->format('d-m-Y H:i:s');

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
            false
        );
    }
    public function exportExcel(Formulario $form)
    {

        $formulario = Formulario::with('campos.opciones_catalogo', 'respuestas.camposRespuestas.campo')->findOrFail($form->id);

        $respuestas = $formulario->respuestas()->with('camposRespuestas.campo')->get();

        $datos = $this->FormularioRepository->generar_informacion_export($respuestas, $formulario);

        $user = auth()->user();

        $fecha = Carbon::now()->format('d-m-Y H:i:s');

        $export = new ExportExcel('formularios.export_respuestas', [
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

        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);

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

        if (!$respuesta) {
            return response()->json(['error' => 'No se encontró la respuesta'], 404);
        }

        $datos = [];
        foreach ($respuesta->camposRespuestas as $cr) {
            $datos[$cr->campo->nombre] = $cr->valor;
        }
        $formulario = Formulario::find($form_id);

        return response()->json([
            'id' => $respuesta->id,
            'form_id' => $respuesta->form_id,
            'nombre' => $formulario->nombre,
            'datos' => $datos
        ]);
    }


    public function obtenerFilaVisor($form_id, $respuesta_id)
    {
        $respuesta = RespuestasForm::with([
            'camposRespuestas',
            'camposRespuestas.campo.opciones_catalogo',
            'formulario.campos'
        ])
            ->where('form_id', $form_id)
            ->where('id', $respuesta_id)
            ->first();

        if (!$respuesta) {
            return response()->json(['error' => 'No se encontró la respuesta'], 404);
        }

        $formulario = $respuesta->formulario;
        $resultado = [];

        foreach ($formulario->campos->sortBy('posicion') as $campo) {
            $valores = $respuesta->camposRespuestas
                ->where('cf_id', $campo->id)
                ->pluck('valor')
                ->toArray();

            $displayValores = [];
            foreach ($valores as $v) {
                $valorResuelto = $this->FormularioRepository->resolverValor($campo, $v);

                // Manejo de tipos especiales (archivos, fecha, etc.)
                $tipoCampo = strtolower($campo->campo_nombre);
                switch ($tipoCampo) {
                    case 'imagen':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/imagenes/{$valorResuelto}");
                        break;

                    case 'video':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/videos/{$valorResuelto}");
                        break;

                    case 'archivo':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/archivos/{$valorResuelto}");
                        break;

                    case 'enlace':
                    case 'hora':
                        $displayValores[] = $valorResuelto;
                        break;

                    case 'fecha':
                        $displayValores[] = Carbon::parse($valorResuelto)->format('d/m/Y');
                        break;

                    default:
                        $displayValores[] = $valorResuelto;
                }
            }

            $resultado[] = [
                'etiqueta' => $campo->etiqueta,
                'tipo' => $tipoCampo,
                'valores' => $displayValores
            ];
        }
        return response()->json([
            'nombre_formulario' => $formulario->nombre,
            'campos' => $resultado
        ]);
    }

    public function getInfo($formDestinoId)
    {
        $formulario = Formulario::with('campos')->find($formDestinoId);

        if (!$formulario) {
            return response()->json(['error' => 'Formulario no encontrado'], 404);
        }

        $formulario->campos = $this->FormularioRepository->CamposFormCat($formulario->campos);

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

}
