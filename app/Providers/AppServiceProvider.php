<?php

namespace App\Providers;
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





    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
