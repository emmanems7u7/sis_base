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
        $fecha = now()->format('Ymd');
        $nombreSeeder = "Generado_SeederMenu_{$fecha}.php";
        $nombreClase = "Generado_SeederMenu_{$fecha}";

        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        // Preparamos los valores
        $nombre = addslashes($menu->nombre);
        $orden = (int) $menu->orden;
        $padreId = $menu->padre_id !== null ? $menu->padre_id : 'null';
        $seccionId = (int) $menu->seccion_id;
        $ruta = addslashes($menu->ruta);
        $modulo = addslashes($menu->modulo_id) ?? 'null';

        $registro = <<<PHP
                                    [
                                        'id' => '{$menu->id}',
                                        'nombre' => '{$nombre}',
                                        'orden' => {$orden},
                                        'padre_id' => {$padreId},
                                        'seccion_id' => {$seccionId},
                                        'ruta' => '{$ruta}',
                                        'modulo_id' => '{$modulo}',
                                    ],
                        PHP;

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                        <?php
                        
                        namespace Database\Seeders;
                        
                        use Illuminate\Database\Seeder;
                        use App\Models\Menu;
                        
                        class Generado_SeederMenu_{$fecha} extends Seeder
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
            return;
        }

        // Si el seeder ya existe, evita duplicados
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'nombre' => '{$nombre}'")) {
            $contenido = str_replace('        $menus = [', "        \$menus = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase);
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
        $fecha = now()->format('Ymd');
        $nombreSeeder = "Generado_SeederSeccion_{$fecha}.php";
        $nombreClase = "Generado_SeederSeccion_{$fecha}";
        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        // Preparar los valores
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

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                    <?php
                    
                    namespace Database\Seeders;
                    
                    use Illuminate\Database\Seeder;
                    use App\Models\Seccion;
                    
                    class Generado_SeederSeccion_{$fecha} extends Seeder
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
            return;
        }

        // Evitar duplicados si ya existe
        $contenido = File::get($rutaSeeder);
        if (!Str::contains($contenido, "'titulo' => '{$titulo}'")) {
            $contenido = str_replace('        $secciones = [', "        \$secciones = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }
        $this->agregarSeederADatabaseSeeder($nombreClase);
    }



    public function ObtenerMenuPorSeccion($seccion_id)
    {
        $menus = Menu::Where('seccion_id', $seccion_id)->orderBy('orden')->get();
        return $menus;
    }

    function eliminarSeccionDeSeeder(int $id)
    {
        $seeders = File::files(database_path('seeders'));

        foreach ($seeders as $seeder) {
            // Solo buscamos los seeders de secciones generados
            if (!Str::startsWith($seeder->getFilename(), 'Generado_SeederSeccion_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());

            // Patrón para eliminar cualquier array que tenga el 'id' de la sección
            $pattern = "/\s*\[\s*'id'\s*=>\s*" . preg_quote($id, '/') . "[^\]]*\],?\s*/";

            $contenidoNuevo = preg_replace($pattern, '', $contenido, 1);

            // Guardamos si hubo cambios
            if ($contenido !== $contenidoNuevo) {
                File::put($seeder->getRealPath(), $contenidoNuevo);
                break; // ya lo encontramos
            }
        }
    }



    public function eliminarMenuDeSeeders(Menu $menu): array
    {
        $archivosModificados = [];
        $seeders = File::files(database_path('seeders'));

        foreach ($seeders as $seeder) {
            if (!Str::startsWith($seeder->getFilename(), 'Generado_SeederMenu_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            // 1️⃣ Intentar eliminar por 'id'
            $patternId = "/\s*\[\s*'id'\s*=>\s*['\"]?" . preg_quote($menu->id, '/') . "['\"]?[^\]]*\],?\s*/";
            $contenido = preg_replace($patternId, '', $contenido, 1);

            // 2️⃣ Si no se eliminó nada por id, intentar por 'nombre'
            if ($contenido === $contenidoOriginal) {
                $nombreEscapado = preg_quote($menu->nombre, '/');
                $patternNombre = "/[ \t]*\[\s*'nombre'\s*=>\s*'{$nombreEscapado}'(?:.*?\n)*?\s*\],\s*/";
                $contenido = preg_replace($patternNombre, '', $contenido, 1);
            }

            // 3️⃣ Guardar si hubo cambios
            if ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
                $archivosModificados[] = $seeder->getFilename();
            }
        }

        return $archivosModificados; // Array con los seeders modificados
    }


}
