<?php
namespace App\Repositories;

use App\Interfaces\MenuInterface;
use App\Interfaces\PermisoInterface;
use App\Interfaces\SeederInterface;
use App\Models\Menu;
use App\Models\Seccion;




class MenuRepository extends BaseRepository implements MenuInterface
{
    protected $permisoRepository;
    protected $SeederRepository;

    public function __construct(PermisoInterface $permisoRepository, SeederInterface $seederRepository)
    {
        $this->permisoRepository = $permisoRepository;
        $this->SeederRepository = $seederRepository;

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

        if ($menu->modulo_id == null) {
            $this->SeederRepository->guardarEnSeederMenu($menu);
            $this->permisoRepository->Store_Permiso($menu->nombre, 'menu', $menu->id);
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
        $this->SeederRepository->guardarEnSeederSeccion($seccion);

        $this->permisoRepository->Store_Permiso($seccion->titulo, 'seccion', $seccion->id);

    }



    public function ObtenerMenuPorSeccion($seccion_id)
    {
        $menus = Menu::Where('seccion_id', $seccion_id)->orderBy('orden')->get();
        return $menus;
    }


    public function eliminarDeSeederSeccion(Seccion $seccion)
    {
        $this->SeederRepository->eliminarDeSeederSeccion($seccion);
    }

    public function eliminarDeSeederMenu(Menu $seccion)
    {
        $this->SeederRepository->eliminarDeSeederMenu($seccion);
    }



}
