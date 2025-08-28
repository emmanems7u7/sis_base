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
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederCategoria_{$fecha}.php";
        $nombreClase = "SeederCategoria_{$fecha}";
        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

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

        // Si no existe, creamos el archivo con la estructura base
        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                    <?php
                    
                    namespace Database\Seeders;
                    
                    use Illuminate\Database\Seeder;
                    use App\Models\Categoria;
                    
                    class SeederCategoria_{$fecha} extends Seeder
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

        // Si ya existe, agregamos el nuevo registro si no está
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'nombre' => '{$nombre}'")) {
            $contenido = str_replace('        $categorias = [', "        \$categorias = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase, 'SEEDERS CATEGORIA');

    }
    protected function eliminarDeSeederCategoria(Categoria $categoria): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederCategoria_{$fecha}.php";

        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        if (!File::exists($rutaSeeder)) {
            return;
        }

        $nombreEscapado = preg_quote($categoria->nombre, '/');
        $contenido = File::get($rutaSeeder);

        // Regex para encontrar el array con ese nombre
        $pattern = "/[ \t]*\[\s*'nombre'\s*=>\s*'{$nombreEscapado}'(?:.*?\n)*?\s*\],\s*/";

        $contenidoModificado = preg_replace($pattern, '', $contenido, 1);

        if ($contenidoModificado !== null && $contenidoModificado !== $contenido) {
            File::put($rutaSeeder, $contenidoModificado);
        }
    }

    protected function guardarEnSeederCatalogo(Catalogo $catalogo): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederCatalogo_{$fecha}.php";
        $nombreClase = "SeederCatalogo_{$fecha}";

        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

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

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                    <?php
                    
                    namespace Database\Seeders;
                    
                    use Illuminate\Database\Seeder;
                    use App\Models\Catalogo;
                    
                    class SeederCatalogo_{$fecha} extends Seeder
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
            return;
        }

        // Si ya existe, evitamos duplicados
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'catalogo_codigo' => '{$codigo}'")) {
            $contenido = str_replace('        $catalogos = [', "        \$catalogos = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }
        $this->agregarSeederADatabaseSeeder($nombreClase, 'SEEDERS CATALOGO');

    }
    protected function eliminarDeSeederCatalogo(Catalogo $catalogo): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederCatalogo_{$fecha}.php";
        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        if (!File::exists($rutaSeeder)) {
            return;
        }

        $codigoEscapado = preg_quote($catalogo->catalogo_codigo, '/');
        $contenido = File::get($rutaSeeder);

        $pattern = "/[ \t]*\[\s*'catalogo_codigo'\s*=>\s*'{$codigoEscapado}'(?:.*?\n)*?\s*\],\s*/";

        $contenidoModificado = preg_replace($pattern, '', $contenido, 1);

        if ($contenidoModificado !== null && $contenidoModificado !== $contenido) {
            File::put($rutaSeeder, $contenidoModificado);
        }
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
}
