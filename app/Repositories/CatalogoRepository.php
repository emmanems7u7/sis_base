<?php
namespace App\Repositories;

use App\Interfaces\CatalogoInterface;

use App\Models\Categoria;

use App\Models\Catalogo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogoRepository extends BaseRepository implements CatalogoInterface
{



    public function __construct()
    {
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
            'accion_usuario' => auth()->user()->name ?? 'sistema',

        ]);
        $this->guardarEnSeederCatalogo($catalogo);

        return $catalogo;
    }

    public function EditarCatalogo($request, $catalogo)
    {

        $catalogo->update([
            'categoria_id' => $this->cleanHtml($request->categoria),
            'catalogo_parent' => $this->cleanHtml($request->catalogo_parent),
            'catalogo_codigo' => $this->cleanHtml($request->catalogo_codigo),
            'catalogo_descripcion' => $this->cleanHtml($request->catalogo_descripcion),
            'catalogo_estado' => $this->cleanHtml($request->catalogo_estado),
        ]);
    }

    public function GuardarCategoria($request)
    {
        $categoria = Categoria::create([
            'nombre' => $this->cleanHtml($request->input('nombre')),
            'descripcion' => $this->cleanHtml($request->input('descripcion')),
            'estado' => $this->cleanHtml($request->input('estado')),
        ]);
        $this->guardarEnSeederCategoria($categoria);
        return $categoria;
    }
    public function EditarCategoria($request, $categoria)
    {
        $categoria->update([
            'nombre' => $this->cleanHtml($request->input('nombre')),
            'descripcion' => $this->cleanHtml($request->input('descripcion')),
            'estado' => $this->cleanHtml($request->input('estado')),
        ]);

    }



    protected function guardarEnSeederCategoria(Categoria $categoria): void
    {
        // Determinar stage y carpeta según tipo
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Categorias';

        $carpetaSeeder = match ($stage) {
            'DEV' => "DEV\Categorias",
            'QA' => "QA\Categorias",
            'PROD' => "PROD\Categorias",
            default => "GEN\Categorias",
        };

        // Timestamp para nombre único
        $fecha = now()->format('Ymd');
        $nombreClase = "SeederCategoria_{$fecha}";
        $rutaSeeder = database_path("seeders/{$carpetaSeeder}/{$nombreClase}.php");

        // Preparamos los valores
        $nombre = addslashes($categoria->nombre);
        $descripcion = addslashes($categoria->descripcion ?? '');
        $estado = addslashes($categoria->estado ?? 'activo');

        $registro = <<<PHP
            [
                'id' => {$categoria->id},
                'nombre' => '{$nombre}',
                'descripcion' => '{$descripcion}',
                'estado' => '{$estado}',
            ],
        PHP;

        // Crear carpeta si no existe
        File::ensureDirectoryExists(database_path("seeders/{$carpetaSeeder}"));

        // Si no existe, creamos el archivo con la estructura base
        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
    <?php
    
    namespace Database\Seeders\\{$carpetaSeeder};
    
    use Illuminate\Database\Seeder;
    use App\Models\Categoria;
    
    class {$nombreClase} extends Seeder
    {
        public function run(): void
        {
            \$categorias = [{$registro}];
    
            foreach (\$categorias as \$data) {
                Categoria::firstOrCreate(
                    ['nombre' => \$data['nombre']],
                    \$data
                );
            }
        }
    }
    PHP;

            File::put($rutaSeeder, $plantilla);
            return;
        }

        // Si ya existe, agregamos el nuevo registro si no está duplicado
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'nombre' => '{$nombre}'")) {
            $contenido = str_replace('        $categorias = [', "        \$categorias = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        // Agregar automáticamente al seeder maestro del stage
        $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
    }



    public function eliminarDeSeederCategoria(Categoria $categoria)
    {
        $archivosModificados = [];
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Categorias';
        $rutaCarpeta = database_path("seeders/{$stage}/{$tipo}");

        if (!File::exists($rutaCarpeta)) {
            return $archivosModificados;
        }

        $seeders = File::files($rutaCarpeta);

        foreach ($seeders as $seeder) {
            // Solo seeders de categorías generados
            if (!Str::contains($seeder->getFilename(), 'SeederCategoria_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            $nombreEscapado = preg_quote($categoria->nombre, '/');

            // Regex para eliminar el array que tenga el 'nombre' de la categoría
            $pattern = "/\[\s*'id'.*?'nombre'\s*=>\s*'{$nombreEscapado}'.*?\],?/s";

            $contenido = preg_replace($pattern, '', $contenido, 1);

            // Limpiar comas sobrantes o líneas vacías
            $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido);
            $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

            if (Str::contains($contenido, '[]')) {
                // Si no quedan registros, eliminar el archivo
                File::delete($seeder->getRealPath());
                $archivosModificados[] = $seeder->getFilename();

                // Quitar la llamada del seeder padre
                $this->eliminarLlamadaDeSeederPadre($stage, $tipo, pathinfo($seeder->getFilename(), PATHINFO_FILENAME));

            } elseif ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
                $archivosModificados[] = $seeder->getFilename();
            }
        }

        return $archivosModificados;
    }



    protected function guardarEnSeederCatalogo(Catalogo $catalogo): void
    {
        // Determinar stage y carpeta según tipo
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Catalogos';
        $carpetaSeeder = match ($stage) {
            'DEV' => "DEV\Catalogos",
            'QA' => "QA\Catalogos",
            'PROD' => "PROD\Catalogos",
            default => "GEN\Catalogos",
        };

        // Timestamp para nombre único
        $fecha = now()->format('Ymd');
        $nombreClase = "SeederCatalogo_{$fecha}";
        $rutaSeeder = database_path("seeders/{$carpetaSeeder}/{$nombreClase}.php");

        // Escapamos valores
        $categoriaId = (int) $catalogo->categoria_id;
        $catalogoParent = $catalogo->catalogo_parent !== null ? (int) $catalogo->catalogo_parent : 'null';
        $codigo = addslashes($catalogo->catalogo_codigo);
        $descripcion = addslashes($catalogo->catalogo_descripcion);
        $estado = addslashes($catalogo->catalogo_estado);
        $accion = addslashes($catalogo->accion_usuario ?? 'sistema');

        $registro = <<<PHP
            [
                'id' => {$catalogo->id},
                'categoria_id' => {$categoriaId},
                'catalogo_parent' => {$catalogoParent},
                'catalogo_codigo' => '{$codigo}',
                'catalogo_descripcion' => '{$descripcion}',
                'catalogo_estado' => '{$estado}',
                'accion_usuario' => '{$accion}',
            ],
        PHP;

        // Crear carpeta si no existe
        File::ensureDirectoryExists(database_path("seeders/{$carpetaSeeder}"));

        // Crear archivo si no existe
        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
    <?php
    
    namespace Database\Seeders\\{$stage}\\{$tipo};
    
    use Illuminate\Database\Seeder;
    use App\Models\Catalogo;
    
    class {$nombreClase} extends Seeder
    {
        public function run(): void
        {
            \$catalogos = [{$registro}];
    
            foreach (\$catalogos as \$data) {
                Catalogo::firstOrCreate(
                    ['catalogo_codigo' => \$data['catalogo_codigo']],
                    \$data
                );
            }
        }
    }
    PHP;
            File::put($rutaSeeder, $plantilla);
            // Agregar automáticamente al seeder padre
            $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
            return;
        }

        // Si ya existe, agregar registro si no está duplicado
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'catalogo_codigo' => '{$codigo}'")) {
            $contenido = str_replace('        $catalogos = [', "        \$catalogos = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        // Agregar al padre, ordenado
        $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
    }


    protected function eliminarDeSeederCatalogo(Catalogo $catalogo): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Catalogos';
        $carpetaSeeder = database_path("seeders/{$stage}/{$tipo}");

        if (!File::exists($carpetaSeeder)) {
            return;
        }

        $seeders = File::files($carpetaSeeder);

        foreach ($seeders as $seeder) {
            if (!Str::contains($seeder->getFilename(), 'SeederCatalogo_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            $codigoEscapado = preg_quote($catalogo->catalogo_codigo, '/');

            $pattern = "/\[\s*'catalogo_codigo'\s*=>\s*'{$codigoEscapado}'.*?\],?/s";

            $contenido = preg_replace($pattern, '', $contenido, 1);

            // Limpiar comas sobrantes y array vacío
            $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido);
            $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

            if (Str::contains($contenido, '[]')) {
                // Si no quedan registros, eliminar el archivo
                File::delete($seeder->getRealPath());

                // Quitar del seeder padre
                $this->eliminarLlamadaDeSeederPadre($stage, $tipo, pathinfo($seeder->getFilename(), PATHINFO_FILENAME));
            } elseif ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
            }
        }
    }

    public function obtenerCatalogosPorCategoria($nombreCategoria, $soloActivos = false)
    {
        $categoria = Categoria::where('nombre', $nombreCategoria)->first();

        if (!$categoria) {
            return collect(); // Retorna colección vacía si no existe
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
            $query->offset($offset); // empiece desde el offset
        }

        if ($limit) {
            $query->limit($limit); // máximo de registros
        }

        return $query->get();
    }

    public function buscarPorDescripcion($categoriaId, $descripcion)
    {
        return Catalogo::where('categoria_id', $categoriaId)
            ->where('catalogo_descripcion', 'like', "%{$descripcion}%")
            ->first();
    }
}
