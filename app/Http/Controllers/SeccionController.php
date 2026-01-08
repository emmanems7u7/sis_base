<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use Illuminate\Http\Request;
use App\Interfaces\MenuInterface;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Interfaces\PermisoInterface;
use Spatie\Permission\Models\Permission;
use App\Interfaces\IAInterface;


class SeccionController extends Controller
{
    protected $menuRepository;
    protected $PermisoRepository;
    protected $IARepository;

    public function __construct(MenuInterface $MenuInterface, PermisoInterface $PermisoInterface, IAInterface $iAInterface)
    {
        $this->PermisoRepository = $PermisoInterface;
        $this->menuRepository = $MenuInterface;
        $this->IARepository = $iAInterface;
    }

    public function index()
    {
        $secciones = Seccion::all();  // Obtener todas las secciones
        return view('secciones.index', compact('secciones'));
    }

    // Mostrar el formulario para crear una nueva sección
    public function create()
    {
        return view('secciones.create');
    }

    // Guardar una nueva sección
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'icono' => 'required|string|max:255',
        ]);

        $this->menuRepository->CrearSeccion($request);

        return redirect()->back()->with('status', 'Sección creada exitosamente.');
    }
    function cambiarSeccion(Request $request)
    {
        $request->validate([
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);
        $seccionId = $request->input('seccion_id');


        $menus = $this->menuRepository->ObtenerMenuPorSeccion($seccionId);
        $sugerido = $menus->max('orden') + 1;
        return response()->json([
            'status' => 'success',
            'sugerido' => $sugerido
        ]);
    }
    // Mostrar el formulario para editar una sección
    public function edit($id)
    {
        $seccion = Seccion::findOrFail($id);
        return view('secciones.edit', compact('seccion'));
    }

    // Actualizar una sección
    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        $seccion = Seccion::findOrFail($id);
        $seccion->update($request->all());  // Actualizar la sección
        return redirect()->route('secciones.index')->with('success', 'Sección actualizada exitosamente.');
    }

    // Eliminar una sección
    public function destroy($id)
    {
        $seccion = Seccion::findOrFail($id);

        $permiso = Permission::where('id_relacion', $seccion->id)->where('name', $seccion->titulo)->first();


        if ($permiso != null) {
            $this->PermisoRepository->eliminarDeSeederPermiso($permiso);
            $this->menuRepository->eliminarDeSeederSeccion($seccion);

            $permiso->delete();

        }

        $seccion->delete();
        return redirect()->back()->with('success', 'Sección eliminada exitosamente.');
    }

    public function SugerirIcono(Request $request)
    {
        $titulo = $request->input('titulo');

        // Instrucción específica para la IA
        $instruccion = 'Eres un asistente que sugiere nombres de iconos de Font Awesome. 
        Responde ÚNICAMENTE con la clase del ícono (ej: "fas fa-user"). 
        No incluyas texto adicional.';

        // Llamar a la función consultarIA
        $respuesta = $this->IARepository->consultarIA(
            "¿Qué ícono de Font Awesome corresponde al título: '$titulo'?",
            $instruccion,
            10,        // max_tokens
            0.3,       // temperature
            'icono'    // tipoConsulta
        );

        // Si la respuesta es un JsonResponse, la devolvemos tal cual (puede ser error)
        if ($respuesta instanceof \Illuminate\Http\JsonResponse) {
            $data = $respuesta->getData(true);

            // Si hay error, retornamos como está
            if (isset($data['error'])) {
                return $respuesta;
            }

            // Validamos el formato del ícono
            $icono = $data['respuesta'] ?? '';
            if (preg_match('/^fas fa-[a-zA-Z0-9-]+$/', $icono)) {
                return response()->json(['icono' => $icono]);
            } else {
                Log::warning('Respuesta inesperada de Llama 3', ['respuesta' => $icono]);
                return response()->json(['icono' => 'fas fa-question']);
            }
        }

        // Si algo raro pasó
        return response()->json(['icono' => 'fas fa-question']);
    }


    public function ordenar(Request $request)
    {
        $configuracion = Configuracion::first();
        if ($configuracion->mantenimiento == 1) {
            foreach ($request->orden as $item) {
                Seccion::where('id', $item['id'])->update(['posicion' => $item['posicion']]);
            }

            return response()->json(['status' => 'success', 'message' => 'Secciones reordenadas correctamente.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'El sistema no esta en modo mantenimiento para realizar el ordenamiento, sus cambios no se guardarán']);

        }

    }

}
