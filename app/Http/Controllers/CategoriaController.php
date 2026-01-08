<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;

class CategoriaController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $CatalogoInterface)
    {

        $this->CatalogoRepository = $CatalogoInterface;
    }
    public function index()
    {

        $categorias = Categoria::all();
        return view('catalogo.index', compact('categorias'));
    }

    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Crear Categoria', 'url' => route('categorias.index')],

        ];

        return view('catalogo.createCategoria', compact('breadcrumb'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:0,1',
        ]);

        $this->CatalogoRepository->GuardarCategoria($request);


        return redirect()->route('catalogos.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Editar Categoria', 'url' => route('categorias.index')],

        ];
        return view('catalogo.editCategoria', compact('id', 'categoria', 'breadcrumb'));
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'required|in:0,1',
        ]);

        $this->CatalogoRepository->EditarCategoria($request, $categoria);


        return redirect()->route('catalogo.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        $this->CatalogoRepository->eliminarDeSeederCategoria($categoria);

        $categoria->delete();

        return redirect()->back()->with('status', 'Categoría eliminada.');
    }
}
