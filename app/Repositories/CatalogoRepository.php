<?php
namespace App\Repositories;

use App\Interfaces\CatalogoInterface;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Support\Str;

use App\Interfaces\SeederInterface;

class CatalogoRepository extends BaseRepository implements CatalogoInterface
{

    protected $SeederRepository;


    public function __construct(SeederInterface $seederRepository)
    {
        $this->SeederRepository = $seederRepository;

        parent::__construct();

    }
    public function GuardarCatalogo($request)
    {
        $catalogo = Catalogo::create([
            'categoria_id' => $this->cleanHtml($request->input('categoria')),
            'catalogo_parent' => $this->cleanHtml($request->input('catalogo_parent')),
            'catalogo_codigo' => $this->cleanHtml($request->input('catalogo_codigo')),
            'catalogo_descripcion' => $this->cleanHtml($request->input('catalogo_descripcion')),
            'catalogo_estado' => $this->cleanHtml($request->input('catalogo_estado')),

        ]);
        $this->SeederRepository->guardarEnSeederCatalogo($catalogo);

        return $catalogo;
    }

    public function EditarCatalogo($request, $catalogo)
    {

        $catalogo->update([
            'categoria_id' => $this->cleanHtml($request->categoria),
            'catalogo_parent' => $this->cleanHtml($request->catalogo_parent),
            'catalogo_descripcion' => $this->cleanHtml($request->catalogo_descripcion),
            'catalogo_estado' => $this->cleanHtml($request->catalogo_estado),
        ]);
    }

    public function GuardarCategoria($request)
    {

        $categoria = Categoria::create([
            'nombre' => $this->cleanHtml($request->input('nombre')),
            'descripcion' => $this->cleanHtml($request->input('descripcion')),
            'estado' => $request->input('estado'),
        ]);
        $this->SeederRepository->guardarEnSeederCategoria($categoria);
        return $categoria;
    }
    public function EditarCategoria($request, $categoria)
    {
        $categoria->update([
            'nombre' => $this->cleanHtml($request->input('nombre')),
            'descripcion' => $this->cleanHtml($request->input('descripcion')),
            'estado' => $request->input('estado'),
        ]);

    }


    public function obtenerCatalogosPorCategoria($nombreCategoria, $soloActivos = false)
    {
        $categoria = Categoria::where('nombre', $nombreCategoria)->first();

        if (!$categoria) {
            return collect();
        }

        $query = Catalogo::where('categoria_id', $categoria->id);

        if ($soloActivos) {
            $query->where('catalogo_estado', 1);
        }

        return $query->get();
    }
    function generarNuevoCodigoCatalogo(int $categoriaId, string $codigoInicial = 'diag-001'): string
    {
        $ultimoCatalogo = Catalogo::where('categoria_id', $categoriaId)
            ->orderByDesc('catalogo_codigo')
            ->first();

        if ($ultimoCatalogo) {
            $codigoUltimo = $ultimoCatalogo->catalogo_codigo;

            if (preg_match('/^([a-zA-Z\-]+)(\d+)$/', $codigoUltimo, $matches)) {
                $prefijo = $matches[1];
                $numero = intval($matches[2]);

                $nuevoNumero = $numero + 1;
                $largoNumero = strlen($matches[2]);

                return $prefijo . str_pad($nuevoNumero, $largoNumero, '0', STR_PAD_LEFT);
            }
        }

        return $codigoInicial;
    }
    public function getNombreCatalogo($catalogo_codigo)
    {
        return Catalogo::where('catalogo_codigo', $catalogo_codigo)
            ->value('catalogo_descripcion') ?? 'No encontrado';
    }

    public function obtenerCatalogosPorCategoriaID($id, $soloActivos = false, $limit = null, $offset = 0)
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return collect();
        }

        $query = Catalogo::where('categoria_id', $categoria->id);

        if ($soloActivos) {
            $query->where('catalogo_estado', 1);
        }

        $query->orderBy('catalogo_descripcion');

        if ($offset) {
            $query->offset($offset);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function buscarPorDescripcion($categoriaId, $descripcion)
    {
        return Catalogo::where('categoria_id', $categoriaId)
            ->where('catalogo_descripcion', 'like', "%{$descripcion}%")
            ->first();
    }

    public function eliminarDeSeederCategoria(Categoria $categoria)
    {
        $this->SeederRepository->eliminarDeSeederCategoria($categoria);
    }


    public function generarPrefijoUnico(string $nombreCategoria): string
    {
        $nombre = Str::upper(Str::ascii($nombreCategoria));

        $nombre = preg_replace('/[^A-Z ]/', '', $nombre);

        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        if ($nombre === '') {
            return $this->generarPrefijoSeguro();
        }

        $palabras = explode(' ', $nombre);
        $primera = $palabras[0] ?? '';

        if (strlen($primera) < 2) {
            return $this->generarPrefijoSeguro();
        }

        $posibles = [];

        if (strlen($primera) >= 3) {
            $posibles[] = substr($primera, 0, 3);
        }

        $posibles[] = substr($primera, 0, 2);

        if (strlen($primera) >= 3) {
            $posibles[] = $primera[0] . $primera[2] . $primera[1];
            $posibles[] = $primera[0] . $primera[1] . 'A';
        }

        foreach (range('A', 'Z') as $letra) {
            $posibles[] = substr($primera, 0, 2) . $letra;
        }

        foreach (array_unique($posibles) as $prefijo) {
            if (strlen($prefijo) < 2) {
                continue;
            }

            $existe = Catalogo::where('catalogo_codigo', 'like', $prefijo . '-%')->exists();
            if (!$existe) {
                return $prefijo;
            }
        }

        return $this->generarPrefijoSeguro();
    }

    protected function generarPrefijoSeguro(): string
    {
        foreach (range('A', 'Z') as $a) {
            foreach (range('A', 'Z') as $b) {
                foreach (range('A', 'Z') as $c) {
                    $prefijo = $a . $b . $c;

                    $existe = Catalogo::where('catalogo_codigo', 'like', $prefijo . '-%')->exists();
                    if (!$existe) {
                        return $prefijo;
                    }
                }
            }
        }

        return 'CAT';
    }

}
