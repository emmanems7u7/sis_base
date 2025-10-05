<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Formulario;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Interfaces\CatalogoInterface;
use App\Exports\ExportPDF;
use Carbon\Carbon;
use App\Exports\ExportExcel;
use Maatwebsite\Excel\Facades\Excel;

class FormularioController extends Controller
{


    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $catalogoInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;

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
        ]);

        Formulario::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
            'estado' => $request->estado,
        ]);

        return redirect()->route('formularios.index')->with('success', 'Formulario creado correctamente.');
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

        $formulario->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
            'estado' => $request->estado,
        ]);

        return redirect()->route('formularios.index')->with('success', 'Formulario actualizado correctamente.');
    }

    public function destroy(Formulario $formulario)
    {
        $formulario->delete();
        return redirect()->route('formularios.index')->with('success', 'Formulario eliminado correctamente.');
    }


    public function showCampos($id)
    {
        $formulario = Formulario::with(['campos.opciones_catalogo'])->findOrFail($id);

        return response()->json([
            'nombre' => $formulario->nombre,
            'descripcion' => $formulario->descripcion,
            'campos' => $formulario->campos
        ]);
    }


    public function exportPdf(Formulario $form)
    {
        // Cargar campos y respuestas
        $formulario = Formulario::with('campos.opciones_catalogo', 'respuestas.camposRespuestas.campo')->findOrFail($form->id);
        $respuestas = $formulario->respuestas()->with('camposRespuestas.campo')->get();
        $datos = $this->generar_informacion_export($respuestas, $formulario);
        $user = auth()->user();

        $fecha = Carbon::now()->format('d-m-Y H:i:s');

        // Exportar con tu librería
        return ExportPDF::exportPdf(
            'formularios.export_respuestas', // vista Blade
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

        // Cargar campos y respuestas
        $formulario = Formulario::with('campos.opciones_catalogo', 'respuestas.camposRespuestas.campo')->findOrFail($form->id);
        $respuestas = $formulario->respuestas()->with('camposRespuestas.campo')->get();
        $datos = $this->generar_informacion_export($respuestas, $formulario);
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
    public function generar_informacion_export($respuestas, $formulario)
    {

        // Preparar datos en forma de tabla dinámica
        $datos = [];
        foreach ($respuestas as $respuesta) {
            $fila = [];

            // Primero agregamos los campos del formulario
            foreach ($formulario->campos->sortBy('posicion') as $campo) {
                $valores = $respuesta->camposRespuestas
                    ->where('cf_id', $campo->id)
                    ->pluck('valor')
                    ->toArray();

                $tipoCampo = strtolower($campo->campo_nombre);
                $display = [];

                foreach ($valores as $v) {
                    switch ($tipoCampo) {
                        case 'checkbox':
                        case 'radio':
                        case 'selector':
                            $desc = $campo->opciones_catalogo->where('catalogo_codigo', $v)->first()?->catalogo_descripcion;
                            $display[] = $desc ?? $v;
                            break;

                        case 'imagen':
                            $path = public_path("archivos/formulario_{$formulario->id}/imagenes/{$v}");
                            if (file_exists($path)) {
                                $base64 = base64_encode(file_get_contents($path));
                                $type = mime_content_type($path);
                                $display[] = "<img src='data:{$type};base64,{$base64}' style='max-width:80px; max-height:80px;' />";
                            }
                            break;

                        case 'video':
                        case 'archivo':
                            $display[] = $v; // Solo mostrar nombre
                            break;

                        case 'fecha':
                            $display[] = \Carbon\Carbon::parse($v)->format('d/m/Y');
                            break;

                        default:
                            $display[] = $v; // Text, Number, Textarea, Email, Password, Color, Hora, Enlace
                    }
                }

                $fila[$campo->etiqueta] = implode(' ', $display);
            }

            // Finalmente agregamos Actor y Registrado al final
            $fila['Actor'] = $respuesta->actor->name ?? 'Anónimo';
            $fila['Registrado'] = $respuesta->created_at->format('d/m/Y H:i');

            $datos[] = $fila;


            $datos[] = $fila;
        }
        return $datos;

    }
}
