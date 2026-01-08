<?php
namespace App\Repositories;

use App\Interfaces\MenuInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use App\Models\Seccion;
use App\Repositories\PermisoRepository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class MenuRepository extends BaseRepository implements MenuInterface
{
    protected $permisoRepository;
    public function __construct(PermisoRepository $permisoRepository)
    {
        $this->permisoRepository = $permisoRepository;
        parent::__construct();

    }
    public function CrearMenu($request)
    {
        $menu = Menu::create([
            'nombre' => $this->cleanHtml($request->input('nombre')),
            'orden' => $this->cleanHtml($request->input('orden', 0)),
            'padre_id' => $this->cleanHtml($request->input('padre_id')) ?: null,
            'seccion_id' => $this->cleanHtml($request->input('seccion_id')),
            'ruta' => $this->cleanHtml($request->input('ruta')),
            'modulo_id' => $this->cleanHtml($request->input('modulo_id', null)),
        ]);


        $this->guardarEnSeederMenu($menu);
        $this->permisoRepository->Store_Permiso($menu->nombre, 'menu', $menu->id);
    }

    protected function guardarEnSeederMenu(Menu $menu): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Menus';
        $carpetaSeeder = match ($stage) {
            'DEV' => "DEV\Menus",
            'QA' => "QA\Menus",
            'PROD' => "PROD\Menus",
            default => "GEN\Menus",
        };

        $fecha = now()->format('Ymd');
        $nombreClase = "SeederMenu_{$fecha}";
        $rutaSeeder = database_path("seeders/{$carpetaSeeder}/{$nombreClase}.php");

        $nombre = addslashes($menu->nombre);
        $orden = (int) $menu->orden;
        $padreId = $menu->padre_id !== null ? $menu->padre_id : 'null';
        $seccionId = (int) $menu->seccion_id;
        $ruta = addslashes($menu->ruta);
        $modulo = $menu->modulo_id !== null ? (int) $menu->modulo_id : 'null';

        $registro = <<<PHP
        [
            'id' => {$menu->id},
            'nombre' => '{$nombre}',
            'orden' => {$orden},
            'padre_id' => {$padreId},
            'seccion_id' => {$seccionId},
            'ruta' => '{$ruta}',
            'modulo_id' => {$modulo},
        ],
    PHP;

        File::ensureDirectoryExists(database_path("seeders/{$carpetaSeeder}"));

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                <?php

                namespace Database\Seeders\\{$stage}\\{$tipo};

                use Illuminate\Database\Seeder;
                use App\Models\Menu;

                class {$nombreClase} extends Seeder
                {
                    public function run(): void
                    {
                        \$menus = [{$registro}];

                        foreach (\$menus as \$data) {
                            Menu::firstOrCreate(
                                ['nombre' => \$data['nombre']],
                                \$data
                            );
                        }
                    }
                }
                PHP;
            File::put($rutaSeeder, $plantilla);
            $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
            return;
        }

        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'nombre' => '{$nombre}'")) {
            $contenido = str_replace('        $menus = [', "        \$menus = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
    }

    public function eliminarDeSeederMenu(Menu $menu): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Menus';
        $carpetaSeeder = database_path("seeders/{$stage}/{$tipo}");

        if (!File::exists($carpetaSeeder)) {
            return;
        }

        $seeders = File::files($carpetaSeeder);

        foreach ($seeders as $seeder) {
            if (!Str::contains($seeder->getFilename(), 'SeederMenu_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            $nombreEscapado = preg_quote($menu->nombre, '/');

            $pattern = "/\[\s*'nombre'\s*=>\s*'{$nombreEscapado}'.*?\],?/s";

            $contenido = preg_replace($pattern, '', $contenido, 1);

            // Limpiar comas sobrantes y array vacío
            $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido);
            $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

            if (Str::contains($contenido, '[]')) {
                // Si no quedan registros, eliminar archivo
                File::delete($seeder->getRealPath());

                // Quitar del seeder padre
                $this->eliminarLlamadaDeSeederPadre($stage, $tipo, pathinfo($seeder->getFilename(), PATHINFO_FILENAME));
            } elseif ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
            }
        }
    }

    public function CrearSeccion($request)
    {

        $ultimaPosicion = Seccion::max('posicion') ?? 0;

        $seccion = Seccion::create(
            [
                'titulo' => $this->cleanHtml($request->input('titulo')),
                'icono' => $this->cleanHtml($request->input('icono')),
                'posicion' => $ultimaPosicion + 1,
            ]
        );
        $this->guardarEnSeederSeccion($seccion);

        $this->permisoRepository->Store_Permiso($seccion->titulo, 'seccion', $seccion->id);

    }

    protected function guardarEnSeederSeccion(Seccion $seccion): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Secciones';
        $carpetaSeeder = match ($stage) {
            'DEV' => "DEV\Secciones",
            'QA' => "QA\Secciones",
            'PROD' => "PROD\Secciones",
            default => "GEN\Secciones",
        };

        $fecha = now()->format('Ymd');
        $nombreClase = "SeederSeccion_{$fecha}";
        $rutaSeeder = database_path("seeders/{$carpetaSeeder}/{$nombreClase}.php");

        $titulo = addslashes($seccion->titulo);
        $icono = addslashes($seccion->icono);
        $posicion = (int) $seccion->posicion;

        $registro = <<<PHP
        [
            'id' => {$seccion->id},
            'titulo' => '{$titulo}',
            'icono' => '{$icono}',
            'posicion' => {$posicion},
        ],
    PHP;

        File::ensureDirectoryExists(database_path("seeders/{$carpetaSeeder}"));

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
            <?php

            namespace Database\Seeders\\{$stage}\\{$tipo};

            use Illuminate\Database\Seeder;
            use App\Models\Seccion;

            class {$nombreClase} extends Seeder
            {
                public function run(): void
                {
                    \$secciones = [{$registro}];

                    foreach (\$secciones as \$data) {
                        Seccion::firstOrCreate(
                            ['titulo' => \$data['titulo']],
                            \$data
                        );
                    }
                }
            }
            PHP;
            File::put($rutaSeeder, $plantilla);
            $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
            return;
        }

        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'titulo' => '{$titulo}'")) {
            $contenido = str_replace('        $secciones = [', "        \$secciones = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
    }



    public function ObtenerMenuPorSeccion($seccion_id)
    {
        $menus = Menu::Where('seccion_id', $seccion_id)->orderBy('orden')->get();
        return $menus;
    }


    public function eliminarDeSeederSeccion(Seccion $seccion): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Secciones';
        $carpetaSeeder = database_path("seeders/{$stage}/{$tipo}");

        if (!File::exists($carpetaSeeder)) {
            return;
        }

        $seeders = File::files($carpetaSeeder);

        foreach ($seeders as $seeder) {
            if (!Str::contains($seeder->getFilename(), 'SeederSeccion_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            // Escapamos el id del modelo
            $idEscapado = preg_quote($seccion->id, '/');

            // Patrón para eliminar todo el array del registro con ese ID
            $pattern = "/\[\s*'id'\s*=>\s*{$idEscapado}.*?\],?\s*/s";

            $contenido = preg_replace($pattern, '', $contenido, 1);

            // Limpiar comas sobrantes y array vacío
            $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido);
            $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

            if (Str::contains($contenido, '[]')) {
                // Si no quedan registros, eliminar archivo
                File::delete($seeder->getRealPath());

                // Quitar del seeder padre
                $this->eliminarLlamadaDeSeederPadre($stage, $tipo, pathinfo($seeder->getFilename(), PATHINFO_FILENAME));
            } elseif ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
            }
        }
    }





}
