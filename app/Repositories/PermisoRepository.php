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

        $this->registrarEnSeeder($permiso, $idRelacion);

        return $permiso;
    }
    protected function registrarEnSeeder(Permission $permiso, $id_relacion = 0)
    {
        $fecha = now()->format('Ymd');
        $nombreClase = 'SeederPermisos_' . $fecha;
        $rutaSeeder = database_path("seeders/{$nombreClase}.php");

        $lineaPermiso = "            ['id' => {$permiso->id}, 'name' => '{$permiso->name}', 'tipo' => '{$permiso->tipo}', 'id_relacion' => {$id_relacion}, 'guard_name' => '{$permiso->guard_name}' ],";

        // Si no existe el seeder, lo creamos
        if (!File::exists($rutaSeeder)) {
            $contenido = <<<PHP
                <?php

                namespace Database\Seeders;

                use Illuminate\Database\Seeder;
                use Spatie\Permission\Models\Permission;

                class {$nombreClase} extends Seeder
                {
                    public function run()
                    {
                        \$permisos = [
                {$lineaPermiso}
                        ];

                        foreach (\$permisos as \$permiso) {
                            Permission::firstOrCreate(
                                ['name' => \$permiso['name'], 'tipo' => \$permiso['tipo']],
                                \$permiso
                            );
                        }
                    }
                }
                PHP;
            File::put($rutaSeeder, $contenido);
            return;
        }

        // Si existe, asegurarnos de no duplicar
        $contenidoActual = File::get($rutaSeeder);
        if (!Str::contains($contenidoActual, "'name' => '{$permiso->name}'")) {
            $contenidoActual = str_replace(
                '$permisos = [',
                '$permisos = [' . PHP_EOL . $lineaPermiso,
                $contenidoActual
            );
            File::put($rutaSeeder, $contenidoActual);
        }

        $this->agregarSeederADatabaseSeeder($nombreClase, 'SEEDERS PERMISOS');
    }
    function eliminarDeSeeder(Permission $permiso)
    {
        $seeders = File::files(database_path('seeders'));

        foreach ($seeders as $seeder) {
            if (!Str::startsWith($seeder->getFilename(), 'SeederPermisos_')) {
                continue;
            }

            $contenido = File::get($seeder->getRealPath());

            // Construimos la línea esperada para borrar
            $pattern = "/\s*\[\s*'name'\s*=>\s*'" . preg_quote($permiso->name, '/') . "',\s*'tipo'\s*=>\s*'" . preg_quote($permiso->tipo, '/') . "',\s*'guard_name'\s*=>\s*'" . preg_quote($permiso->guard_name, '/') . "'\s*\],?\s*/";

            $contenidoNuevo = preg_replace($pattern, '', $contenido, 1);

            // Si cambió algo, guardamos
            if ($contenido !== $contenidoNuevo) {
                File::put($seeder->getRealPath(), $contenidoNuevo);
                break; // lo encontramos, no buscamos más
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
