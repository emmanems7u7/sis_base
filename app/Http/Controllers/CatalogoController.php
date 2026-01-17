<?php
namespace App\Http\Controllers;
use App\Models\Categoria;

use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Interfaces\CatalogoInterface;

class CatalogoController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $CatalogoInterface)
    {

        $this->CatalogoRepository = $CatalogoInterface;
    }

    public function index(Request $request)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
        ];
        $search_c = $request->get('search_c');

        $categorias = Categoria::when($search_c, function ($query, $search_c) {
            $query->where('nombre', 'like', "%{$search_c}%")
                ->orWhere('descripcion', 'like', "%{$search_c}%");
        })

            ->paginate(9, ['*'], 'categorias')
            ->withQueryString();

        $search = $request->get('search');



        $catalogos = Catalogo::with('categoria')
            ->when($search, function ($query, $search) {
                return $query->where('catalogo_codigo', 'like', "%{$search}%")
                    ->orWhere('catalogo_descripcion', 'like', "%{$search}%")
                    ->orWhereHas('categoria', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%");
                    });
            })
            ->paginate(15);
        return view('catalogo.index', compact('catalogos', 'categorias', 'breadcrumb'));
    }

    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Crear Catalogo', 'url' => route('catalogos.index')],

        ];
        $catalogos = Catalogo::all();
        $categorias = Categoria::all();
        return view('catalogo.create', compact('catalogos', 'breadcrumb', 'categorias'));

    }

    public function store(Request $request)
    {

        $request->validate([
            'categoria' => 'required|exists:categorias,id',
            'catalogo_parent' => 'nullable|string|max:5',
            'catalogo_codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('catalogos')->where(function ($query) use ($request) {
                    return $query->where('categoria_id', $request->categoria);
                }),
            ],
            'catalogo_descripcion' => 'required|string|max:100',
            'catalogo_estado' => 'required|integer|in:0,1',
        ]);

        $this->CatalogoRepository->GuardarCatalogo($request);

        return redirect()->route('catalogos.index')->with('success', 'Catálogo creado correctamente.');
    }


    public function edit($id)
    {
        $catalogo = Catalogo::findOrFail($id);
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Crear Catalogo', 'url' => route('catalogos.index')],

        ];
        $catalogos = Catalogo::where('id', '!=', $catalogo->id)->get();

        $categorias = Categoria::all();

        return view('catalogo.edit', compact('id', 'catalogo', 'catalogos', 'categorias', 'breadcrumb'));
    }

    public function update(Request $request, $id)
    {
        $catalogo = Catalogo::findOrFail($id);

        $request->validate([
            'categoria' => 'required|exists:categorias,id',
            'catalogo_parent' => 'nullable|string|max:5',
            'catalogo_codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('catalogos')->where(function ($query) use ($request) {
                    return $query->where('categoria_id', $request->categoria);
                })->ignore($catalogo->id),
            ],
            'catalogo_descripcion' => 'required|string|max:100',
            'catalogo_estado' => 'required|integer|in:0,1',
        ]);

        $this->CatalogoRepository->EditarCatalogo($request, $catalogo);


        return redirect()->route('catalogos.index')->with('success', 'Catálogo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $catalogo = Catalogo::findOrFail($id);

        $catalogo->delete();

        return redirect()->back()->with('success', 'Catálogo eliminado correctamente.');
    }

    public function ultimoCodigo($categoriaId)
    {
        //  extraer ultimo codigo
        $ultimo = Catalogo::where('categoria_id', $categoriaId)
            ->orderByDesc('id')
            ->value('catalogo_codigo');

        if ($ultimo) {
            return response()->json([
                'codigo' => $ultimo
            ]);
        }

        $categoria = Categoria::findOrFail($categoriaId);

        $prefijo = $this->CatalogoRepository->generarPrefijoUnico($categoria->nombre);

        return response()->json([
            'codigo' => $prefijo . '-000'
        ]);
    }


}

