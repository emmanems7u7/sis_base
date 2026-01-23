<?php

namespace App\Repositories;

use App\Interfaces\SeederInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Catalogo;
use App\Models\Categoria;
use App\Models\Menu;
use App\Models\Seccion;
use Spatie\Permission\Models\Permission;
class SeederRepository extends BaseRepository implements SeederInterface
{

    /* =====================================================
     |  HELPERS GENERALES
     ===================================================== */

    private function resolverSeeder(string $tipo, string $prefijoClase): array
    {
        $stage = strtoupper(env('APP_STAGE'));

        $carpeta = match ($stage) {
            'DEV' => "DEV\\{$tipo}",
            'QA' => "QA\\{$tipo}",
            'PROD' => "PROD\\{$tipo}",
            default => "GEN\\{$tipo}",
        };

        $fecha = now()->format('Ymd');
        $clase = "{$prefijoClase}_{$fecha}";
        $ruta = database_path("seeders/{$carpeta}/{$clase}.php");

        return compact('stage', 'carpeta', 'clase', 'ruta');
    }

    private function crearSeederBase(
        string $ruta,
        string $namespace,
        string $modelo_ruta,
        string $modelo,
        string $clase,
        string $variable,
        string $registro,
        string $claveUnica
    ): void {
        $plantilla = <<<PHP
                        <?php

                        namespace {$namespace};

                        use Illuminate\Database\Seeder;
                        use {$modelo_ruta};

                        class {$clase} extends Seeder
                        {
                            public function run(): void
                            {
                                \${$variable} = [
                        {$registro}
                                ];

                                foreach (\${$variable} as \$data) {
                                    {$modelo}::firstOrCreate(
                                        {$claveUnica},
                                        \$data
                                    );
                                }
                            }
                        }
                        PHP;

        File::put($ruta, $plantilla);
    }

    private function insertarRegistro(
        string $ruta,
        string $needle,
        string $registro,
        string $arrayName
    ): void {
        $contenido = File::get($ruta);

        if (!Str::contains($contenido, $needle)) {
            $contenido = str_replace(
                "        \${$arrayName} = [",
                "        \${$arrayName} = [\n{$registro}",
                $contenido
            );
            File::put($ruta, $contenido);
        }
    }

    private function eliminarRegistro(
        string $ruta,
        string $pattern,
        string $stage,
        string $tipo,
        string $clase
    ): void {
        $contenido = File::get($ruta);
        $original = $contenido;

        $contenido = preg_replace($pattern, '', $contenido, 1);
        $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido);
        $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

        if (Str::contains($contenido, '[]')) {
            File::delete($ruta);
            $this->eliminarLlamadaDeSeederPadre($stage, $tipo, $clase);
        } elseif ($contenido !== $original) {
            File::put($ruta, $contenido);
        }
    }

    private function procesarEliminacion(
        string $tipo,
        string $prefijoSeeder,
        string $pattern
    ): void {
        $stage = strtoupper(env('APP_STAGE'));
        $ruta = database_path("seeders/{$stage}/{$tipo}");

        // Si no existe la carpeta, no hay nada que eliminar
        if (!File::exists($ruta)) {
            return;
        }

        foreach (File::files($ruta) as $seeder) {

            // Solo seeders del tipo correcto
            if (!Str::contains($seeder->getFilename(), $prefijoSeeder)) {
                continue;
            }

            $clase = pathinfo($seeder->getFilename(), PATHINFO_FILENAME);

            // Elimina el registro dentro del seeder
            $this->eliminarRegistro(
                $seeder->getRealPath(),
                $pattern,
                $stage,
                $tipo,
                $clase
            );
        }
    }

    /* =====================================================
     |  MENUS
     ===================================================== */

    function guardarEnSeederMenu(Menu $menu)
    {
        $data = $this->resolverSeeder('Menus', 'SeederMenu');
        File::ensureDirectoryExists(database_path("seeders/{$data['carpeta']}"));

        $registro = <<<PHP
            [
                'id' => {$menu->id},
                'nombre' => '{$menu->nombre}',
                'orden' => {$menu->orden},
                'padre_id' => {$menu->padre_id},
                'seccion_id' => {$menu->seccion_id},
                'ruta' => '{$menu->ruta}',
                'modulo_id' => {$menu->modulo_id},
            ],
PHP;



        if (!File::exists($data['ruta'])) {
            $this->crearSeederBase(
                $data['ruta'],
                "Database\\Seeders\\{$data['stage']}\\Menus",
                "\App\Models\Menu",
                'Menu',
                $data['clase'],
                'menus',
                $registro,
                "['nombre' => \$data['nombre']]"
            );
        } else {
            $this->insertarRegistro($data['ruta'], "'nombre' => '{$menu->nombre}'", $registro, 'menus');
        }

        $this->agregarSeederADatabaseSeeder($data['clase'], $data['stage'], 'Menus');
    }

    public function eliminarDeSeederMenu($menu): void
    {
        $this->procesarEliminacion(
            'Menus',
            'SeederMenu_',
            "/\[\s*'nombre'\s*=>\s*'" . preg_quote($menu->nombre, '/') . "'.*?\],?/s"
        );
    }

    /* =====================================================
     |  SECCIONES
     ===================================================== */

    public function guardarEnSeederSeccion(Seccion $seccion): void
    {
        $data = $this->resolverSeeder('Secciones', 'SeederSeccion');
        File::ensureDirectoryExists(database_path("seeders/{$data['carpeta']}"));

        $registro = <<<PHP
            [
                'id' => {$seccion->id},
                'titulo' => '{$seccion->titulo}',
                'icono' => '{$seccion->icono}',
                'posicion' => {$seccion->posicion},
            ],
PHP;

        if (!File::exists($data['ruta'])) {
            $this->crearSeederBase(
                $data['ruta'],
                "Database\\Seeders\\{$data['stage']}\\Secciones",
                '\App\Models\Seccion',
                'Seccion',
                $data['clase'],
                'secciones',
                $registro,
                "['titulo' => \$data['titulo']]"
            );
        } else {
            $this->insertarRegistro($data['ruta'], "'titulo' => '{$seccion->titulo}'", $registro, 'secciones');
        }

        $this->agregarSeederADatabaseSeeder($data['clase'], $data['stage'], 'Secciones');
    }

    public function eliminarDeSeederSeccion($seccion): void
    {
        $this->procesarEliminacion(
            'Secciones',
            'SeederSeccion_',
            "/\[\s*'titulo'\s*=>\s*'" . preg_quote($seccion->titulo, '/') . "'.*?\],?/s"
        );
    }

    /* =====================================================
     |  PERMISOS
     ===================================================== */

    public function guardarEnSeederPermiso($permiso, $id_relacion = 0): void
    {
        $data = $this->resolverSeeder('Permisos', 'SeederPermisos');
        File::ensureDirectoryExists(database_path("seeders/{$data['carpeta']}"));

        $registro = <<<PHP
            [
                'id' => {$permiso->id},
                'name' => '{$permiso->name}',
                'tipo' => '{$permiso->tipo}',
                'id_relacion' => {$id_relacion},
                'guard_name' => '{$permiso->guard_name}',
            ],
PHP;

        if (!File::exists($data['ruta'])) {
            $this->crearSeederBase(
                $data['ruta'],
                "Database\\Seeders\\{$data['stage']}\\Permisos",
                '\Spatie\Permission\Models\Permission',
                'Permission',
                $data['clase'],
                'permisos',
                $registro,
                "['name' => \$data['name'], 'tipo' => \$data['tipo']]"
            );
        } else {
            $this->insertarRegistro($data['ruta'], "'name' => '{$permiso->name}'", $registro, 'permisos');
        }

        $this->agregarSeederADatabaseSeeder($data['clase'], $data['stage'], 'Permisos');
    }

    public function eliminarDeSeederPermiso($permiso): void
    {
        $this->procesarEliminacion(
            'Permisos',
            'SeederPermisos_',
            "/\[\s*'name'\s*=>\s*'" . preg_quote($permiso->name, '/') . "'\s*,\s*'tipo'\s*=>\s*'" . preg_quote($permiso->tipo, '/') . "'.*?\],?/s"
        );
    }
    /* =====================================================
     |  CATEGORIAS
     ===================================================== */

    public function guardarEnSeederCategoria(Categoria $categoria): void
    {
        $data = $this->resolverSeeder('Categorias', 'SeederCategoria');
        File::ensureDirectoryExists(database_path("seeders/{$data['carpeta']}"));

        $registro = <<<PHP
            [
                'id' => {$categoria->id},
                'nombre' => '{$categoria->nombre}',
                'descripcion' => '{$categoria->descripcion}',
                'estado' => '{$categoria->estado}',
            ],
PHP;

        if (!File::exists($data['ruta'])) {
            $this->crearSeederBase(
                $data['ruta'],
                "Database\\Seeders\\{$data['stage']}\\Categorias",
                '\App\Models\Categoria',
                'Categoria',
                $data['clase'],
                'categorias',
                $registro,
                "['nombre' => \$data['nombre']]"
            );
        } else {
            $this->insertarRegistro($data['ruta'], "'nombre' => '{$categoria->nombre}'", $registro, 'categorias');
        }

        $this->agregarSeederADatabaseSeeder($data['clase'], $data['stage'], 'Categorias');
    }

    public function eliminarDeSeederCategoria($categoria): void
    {
        $this->procesarEliminacion(
            'Categorias',
            'SeederCategoria_',
            "/\[\s*'nombre'\s*=>\s*'" . preg_quote($categoria->nombre, '/') . "'.*?\],?/s"
        );
    }
    /* =====================================================
     |  CATALOGOS
     ===================================================== */

    public function guardarEnSeederCatalogo($catalogo): void
    {
        $data = $this->resolverSeeder('Catalogos', 'SeederCatalogo');
        File::ensureDirectoryExists(database_path("seeders/{$data['carpeta']}"));

        $registro = <<<PHP
            [
                'id' => {$catalogo->id},
                'categoria_id' => {$catalogo->categoria_id},
                'catalogo_parent' => {$catalogo->catalogo_parent},
                'catalogo_codigo' => '{$catalogo->catalogo_codigo}',
                'catalogo_descripcion' => '{$catalogo->catalogo_descripcion}',
                'catalogo_estado' => '{$catalogo->catalogo_estado}',
            ],
PHP;

        if (!File::exists($data['ruta'])) {
            $this->crearSeederBase(
                $data['ruta'],
                "Database\\Seeders\\{$data['stage']}\\Catalogos",
                '\App\Models\Catalogo',
                'Catalogo',
                $data['clase'],
                'catalogos',
                $registro,
                "['catalogo_codigo' => \$data['catalogo_codigo']]"
            );
        } else {
            $this->insertarRegistro($data['ruta'], "'catalogo_codigo' => '{$catalogo->catalogo_codigo}'", $registro, 'catalogos');
        }

        $this->agregarSeederADatabaseSeeder($data['clase'], $data['stage'], 'Catalogos');
    }

    public function eliminarDeSeederCatalogo($catalogo): void
    {
        $this->procesarEliminacion(
            'Catalogos',
            'SeederCatalogo_',
            "/\[\s*'catalogo_codigo'\s*=>\s*'" . preg_quote($catalogo->catalogo_codigo, '/') . "'.*?\],?/s"
        );
    }
}
