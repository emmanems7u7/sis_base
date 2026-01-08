<?php
namespace App\Repositories;

use App\Interfaces\PermisoInterface;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Menu;
use App\Models\Seccion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class PermisoRepository extends BaseRepository implements PermisoInterface
{
    protected $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->permissions = Permission::all();
    }
    public function GetPermisosTipo($tipo)
    {

        return $this->permissions->where('tipo', $tipo);
    }
    public function GetPermisoMenu($id, $rol_id)
    {

        $permission = Permission::findOrFail($id);

        $menus = Seccion::with('menus')->find($permission->id_relacion)->menus;

        if ($rol_id != -1) {
            $role = Role::find($rol_id);

        } else {
            $role = Role::all();

        }

        $permisos_menu = $permission->where('tipo', 'menu')->get();

        foreach ($permisos_menu as $permiso_menu) {
            foreach ($menus as $menu) {
                if ($permiso_menu->id_relacion == $menu->id) {

                    $permission = $permission->where('id_relacion', $menu->id)->where('tipo', 'menu')->first();

                    if ($rol_id != -1) {
                        if ($role->hasPermissionTo($permission)) {
                            $permission->check = true;
                        } else {
                            $permission->check = false;
                        }
                    }
                    $permisosPorTipo[] = $permission;

                }
            }

        }
        return $permisosPorTipo;
    }
    public function GetPermisoTipo($id, $tipo)
    {

    }
    function CrearPermiso($request)
    {
        $this->Store_Permiso($request->name, 'permiso', null, true);
    }

    public function Store_Permiso(string $nombre, string $tipo, ?int $idRelacion = null, bool $soloCrear = false): Permission
    {
        $data = [
            'name' => $this->cleanHtml($nombre),
            'tipo' => $tipo,
            'guard_name' => 'web',
        ];

        if (!$soloCrear) {
            $permiso = Permission::firstOrCreate(
                ['name' => $data['name'], 'tipo' => $tipo],
                ['id_relacion' => $idRelacion] + $data
            );
        } else {
            $permiso = Permission::create($data);
        }

        $this->guardarEnSeederPermiso($permiso, $idRelacion);

        return $permiso;
    }
    protected function guardarEnSeederPermiso(Permission $permiso, $id_relacion = 0): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Permisos';
        $carpetaSeeder = match ($stage) {
            'DEV' => "DEV\Permisos",
            'QA' => "QA\Permisos",
            'PROD' => "PROD\Permisos",
            default => "GEN\Permisos",
        };

        $fecha = now()->format('Ymd');
        $nombreClase = "SeederPermisos_{$fecha}";
        $rutaSeeder = database_path("seeders/{$carpetaSeeder}/{$nombreClase}.php");

        $name = addslashes($permiso->name);
        $tipoPermiso = addslashes($permiso->tipo);
        $guard = addslashes($permiso->guard_name);

        $registro = <<<PHP
            [
                'id' => {$permiso->id},
                'name' => '{$name}',
                'tipo' => '{$tipoPermiso}',
                'id_relacion' => {$id_relacion},
                'guard_name' => '{$guard}',
            ],
        PHP;

        File::ensureDirectoryExists(database_path("seeders/{$carpetaSeeder}"));

        if (!File::exists($rutaSeeder)) {
            $plantilla = <<<PHP
    <?php
    
    namespace Database\Seeders\\{$stage}\\{$tipo};
    
    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Permission;
    
    class {$nombreClase} extends Seeder
    {
        public function run(): void
        {
            \$permisos = [{$registro}];
    
            foreach (\$permisos as \$permiso) {
                Permission::firstOrCreate(
                    ['name' => \$permiso['name'], 'tipo' => \$permiso['tipo']],
                    \$permiso
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
        if (!Str::contains($contenido, "'name' => '{$name}'")) {
            $contenido = str_replace('        $permisos = [', "        \$permisos = [\n{$registro}", $contenido);
            File::put($rutaSeeder, $contenido);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase, $stage, $tipo);
    }


    public function eliminarDeSeederPermiso(Permission $permiso): void
    {
        $stage = strtoupper(env('APP_STAGE'));
        $tipo = 'Permisos';
        $carpetaSeeder = database_path("seeders/{$stage}/{$tipo}");

        if (!File::exists($carpetaSeeder)) {
            return;
        }

        $seeders = File::files($carpetaSeeder);

        foreach ($seeders as $seeder) {
            if (!Str::contains($seeder->getFilename(), 'SeederPermisos_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());
            $contenidoOriginal = $contenido;

            $nameEscapado = preg_quote($permiso->name, '/');
            $tipoEscapado = preg_quote($permiso->tipo, '/');
            $guardEscapado = preg_quote($permiso->guard_name, '/');

            $pattern = "/\[\s*'id'\s*=>\s*\d+\s*,\s*'name'\s*=>\s*'{$nameEscapado}'\s*,\s*'tipo'\s*=>\s*'{$tipoEscapado}'\s*,[^\]]*'guard_name'\s*=>\s*'{$guardEscapado}'\s*\],?/s";

            $contenido = preg_replace($pattern, '', $contenido, 1);
            $contenido = preg_replace("/,\s*(\s*\])/s", "$1", $contenido); // limpiar comas sobrantes
            $contenido = preg_replace("/\[\s*\]/s", '[]', $contenido);

            if (Str::contains($contenido, '[]')) {
                // Archivo vacío, eliminar
                File::delete($seeder->getRealPath());
                $this->eliminarLlamadaDeSeederPadre($stage, $tipo, pathinfo($seeder->getFilename(), PATHINFO_FILENAME));
            } elseif ($contenido !== $contenidoOriginal) {
                File::put($seeder->getRealPath(), $contenido);
            }
        }
    }


    public function EditarPermiso($request, $permission)
    {

        $permission->update([
            'name' => $request->name,
        ]);

    }
}
