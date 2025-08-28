<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Formulario;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Interfaces\CatalogoInterface;

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
        return view('formularios.create', compact('categorias', 'estado_formularios', 'breadcrumb'));
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
}
