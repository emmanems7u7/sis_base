<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formulario;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
class CamposFormController extends Controller
{
    protected $CatalogoRepository;

    public function __construct(CatalogoInterface $catalogoInterface)
    {
        $this->CatalogoRepository = $catalogoInterface;
    }

    // Mostrar listado y formulario de creación/edición
    public function index(Formulario $formulario)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Campos', 'url' => route('formularios.campos.index', $formulario)],
        ];
        $campos = CamposForm::where('form_id', $formulario->id)
            ->orderBy('posicion')
            ->get();

        $categorias = Categoria::all();
        $campos_formulario = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Campos Formulario', true);

        // Cargar opciones de catálogo para cada campo con categoría
        foreach ($campos as $campo) {
            if ($campo->categoria_id) {
                $campo->opciones_catalogo = $this->CatalogoRepository
                    ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
            } else {
                $campo->opciones_catalogo = collect();
            }
        }

        return view('formularios.campos.index', compact('breadcrumb', 'formulario', 'campos', 'categorias', 'campos_formulario'));
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
}
