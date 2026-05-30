<?php

namespace App\Providers;

use App\Interfaces\CategoriaInterface;
use App\Repositories\CategoriaRepository;
use App\Interfaces\ModuloInterface;
use App\Repositories\ModuloRepository;
use App\Interfaces\RespuestasCampoInterface;
use App\Repositories\RespuestasCampoRepository;
use App\Interfaces\FormConfigInterface;
use App\Repositories\FormConfigRepository;
use App\Interfaces\RespuestasFormInterface;
use App\Repositories\RespuestasFormRepository;
use App\Interfaces\CamposFormInterface;
use App\Repositories\CamposFormRepository;
use App\Interfaces\SeederInterface;
use App\Repositories\SeederRepository;
use App\Interfaces\FormLogicInterface;
use App\Repositories\FormLogicRepository;
use App\Interfaces\FormularioInterface;
use App\Repositories\FormularioRepository;
use App\Interfaces\IAInterface;
use App\Repositories\IARepository;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserInterface;
use App\Repositories\UserRepository;

use App\Interfaces\RoleInterface;
use App\Repositories\RoleRepository;
use App\Interfaces\PermisoInterface;
use App\Repositories\PermisoRepository;
use App\Interfaces\MenuInterface;
use App\Repositories\MenuRepository;
use App\Interfaces\CorreoInterface;
use App\Repositories\CorreoRepository;
use App\Interfaces\CatalogoInterface;
use App\Repositories\CatalogoRepository;
use App\Interfaces\NotificationInterface;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Auth;

use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Seccion;
use App\Models\Configuracion;
use App\Models\ConfiguracionCredenciales;
use App\Models\UserPersonalizacion;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CatalogoInterface::class, CatalogoRepository::class);
        $this->app->bind(MenuInterface::class, MenuRepository::class);
        $this->app->bind(CorreoInterface::class, CorreoRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(RoleInterface::class, RoleRepository::class);
        $this->app->bind(PermisoInterface::class, PermisoRepository::class);
        $this->app->bind(IAInterface::class, IARepository::class);
        $this->app->bind(FormularioInterface::class, FormularioRepository::class);
        $this->app->bind(FormLogicInterface::class, FormLogicRepository::class);
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
        $this->app->bind(SeederInterface::class, SeederRepository::class);
        $this->app->bind(CamposFormInterface::class, CamposFormRepository::class);
        $this->app->bind(RespuestasFormInterface::class, RespuestasFormRepository::class);
        $this->app->bind(FormConfigInterface::class, FormConfigRepository::class);
        $this->app->bind(RespuestasCampoInterface::class, RespuestasCampoRepository::class);
        $this->app->bind(ModuloInterface::class, ModuloRepository::class);
        $this->app->bind(CategoriaInterface::class, CategoriaRepository::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $agent = new Agent();

        // Mobile
        View::share('isMobile', $agent->isMobile());

        // Secciones
        $secciones = Cache::rememberForever('sidebar_secciones', function () {

            return Seccion::with('menus')
                ->orderBy('posicion')
                ->get();
        });

        // Config credenciales
        $config = Cache::rememberForever('config_credenciales', function () {

            return ConfiguracionCredenciales::first();
        });

        // Config general
        $configuracion = Cache::rememberForever('configuracion_general', function () {

            return Configuracion::first();
        });


        // Globales
        View::share([
            'secciones' => $secciones,
            'config' => $config,
            'configuracion' => $configuracion,
        ]);
    }
}
