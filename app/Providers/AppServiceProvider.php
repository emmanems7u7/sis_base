<?php

namespace App\Providers;
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



    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
