<?php

namespace App\Http\Controllers;

use App\Models\Correo;
use App\Models\PlantillaCorreo;
use App\Models\VariablesPlantillas;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Interfaces\CorreoInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class CorreoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $correoRepository;
    public function __construct(CorreoInterface $CorreoInterface)
    {

        $this->correoRepository = $CorreoInterface;
    }
    public function index()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Plantillas', 'url' => route('plantillas.index')],
        ];

        $plantillas = PlantillaCorreo::orderBy('id', 'desc')->get();

        return view('plantillas.index', compact(
            'plantillas',
            'breadcrumb',

        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Plantillas', 'url' => route('plantillas.index')],
            ['name' => 'Crear Plantilla', 'url' => route('plantillas.create')],

        ];

        return view('plantillas.create', compact('breadcrumb'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:100|unique:plantilla_correos,nombre',
            'contenido' => 'required|string',
            'estado' => 'required|boolean',
        ]);

        $this->correoRepository->CrearPlantilla($request);

        return redirect()->route('plantillas.index')
            ->with('success', 'Plantilla creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function GetPlantilla($id)
    {
        try {
            $email = PlantillaCorreo::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'email' => $email
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Plantilla no encontrada.'
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PlantillaCorreo $plantilla)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Plantillas', 'url' => route('plantillas.index')],
            ['name' => 'Crear Plantilla', 'url' => route('plantillas.create')],

        ];

        // Ruta completa del archivo
        $archivoPath = public_path('plantillas_correos/' . $plantilla->archivo);

        // Leer contenido si existe
        $contenido = '';
        if (file_exists($archivoPath)) {
            $contenido = file_get_contents($archivoPath);
        }

        return view('plantillas.edit', compact('contenido', 'plantilla', 'breadcrumb'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PlantillaCorreo $plantilla)
    {


        $request->validate([
            'nombre' => 'required|string|max:100|unique:plantilla_correos,nombre,' . $plantilla->id,
            'contenido' => 'required|string',
            'estado' => 'required|boolean',
        ]);

        $plantilla = $this->correoRepository->EditarPlantilla($request, $plantilla);

        return redirect()->route('plantillas.index')
            ->with('success', 'Plantilla actualizada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PlantillaCorreo $plantilla)
    {

        $this->correoRepository->EliminarPlantilla($plantilla);

        $plantilla->delete();

        return back()->with('success', 'Plantilla eliminada');
    }
}
