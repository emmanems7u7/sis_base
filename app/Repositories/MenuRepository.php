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
            'accion_usuario' => Auth::user()->na
        ]);


        $this->guardarEnSeederMenu($menu);
        $this->permisoRepository->Store_Permiso($menu->nombre, 'menu', $menu->id);
    }

    protected function guardarEnSeederMenu(Menu $menu): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederMenu_{$fecha}.php";
        $nombreClase = "SeederMenu_{$fecha}";

        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        // Preparamos los valores
        $nombre = addslashes($menu->nombre);
        $orden = (int) $menu->orden;
        $padreId = $menu->padre_id !== null ? $menu->padre_id : 'null';
        $seccionId = (int) $menu->seccion_id;
        $ruta = addslashes($menu->ruta);
        $accionUsuario = addslashes($menu->accion_usuario);

        $registro = <<<PHP
                                    [
                                        'id' => '{$menu->id}',
                                        'nombre' => '{$nombre}',
                                        'orden' => {$orden},
                                        'padre_id' => {$padreId},
                                        'seccion_id' => {$seccionId},
                                        'ruta' => '{$ruta}',
                                        'accion_usuario' => '{$accionUsuario}',
                                    ],
                        PHP;

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                        <?php
                        
                        namespace Database\Seeders;
                        
                        use Illuminate\Database\Seeder;
                        use App\Models\Menu;
                        
                        class SeederMenu_{$fecha} extends Seeder
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

        $this->agregarSeederADatabaseSeeder($nombreClase, 'SEEDERS MENU');
    }
    public function eliminarDeSeederMenu(Menu $menu): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederMenu_{$fecha}.php";
        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        if (!File::exists($rutaSeeder)) {
            return;
        }

        $nombreEscapado = preg_quote($menu->nombre, '/');
        $contenido = File::get($rutaSeeder);

        // Regex robusto para encontrar un array que contenga 'nombre' => 'el_nombre'
        $pattern = "/[ \t]*\[\s*'nombre'\s*=>\s*'{$nombreEscapado}'(?:.*?\n)*?\s*\],\s*/";

        $contenidoModificado = preg_replace($pattern, '', $contenido, 1);

        if ($contenidoModificado !== null && $contenidoModificado !== $contenido) {
            File::put($rutaSeeder, $contenidoModificado);
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
                'accion_usuario' => Auth::user()->name,
            ]
        );
        $this->guardarEnSeederSeccion($seccion);
        $this->permisoRepository->Store_Permiso($seccion->titulo, 'seccion', $seccion->id);

    }

    protected function guardarEnSeederSeccion(Seccion $seccion): void
    {
        $fecha = now()->format('Ymd');
        $nombreSeeder = "SeederSeccion_{$fecha}.php";
        $nombreClase = "SeederSeccion_{$fecha}";
        $rutaSeeder = database_path("seeders/{$nombreSeeder}");

        // Preparar los valores
        $titulo = addslashes($seccion->titulo);
        $icono = addslashes($seccion->icono);
        $posicion = (int) $seccion->posicion;
        $accionUsuario = addslashes($seccion->accion_usuario);

        $registro = <<<PHP
                                [
                                    'id' => {$seccion->id},
                                    'titulo' => '{$titulo}',
                                    'icono' => '{$icono}',
                                    'posicion' => {$posicion},
                                    'accion_usuario' => '{$accionUsuario}',
                                ],
                    PHP;

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
                    <?php
                    
                    namespace Database\Seeders;
                    
                    use Illuminate\Database\Seeder;
                    use App\Models\Seccion;
                    
                    class SeederSeccion_{$fecha} extends Seeder
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
        $this->agregarSeederADatabaseSeeder($nombreClase, 'SEEDERS SECCION');
    }



    public function ObtenerMenuPorSeccion($seccion_id)
    {
        $menus = Menu::Where('seccion_id', $seccion_id)->orderBy('orden')->get();
        return $menus;
    }
}
