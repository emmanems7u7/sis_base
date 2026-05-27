<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

use App\Models\UserPersonalizacion;
use App\Models\ConfiguracionCredenciales;
use Illuminate\Support\Facades\File;
class ShareUserDataMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        $preferencias = null;
        $sidebarColor = 'primary';
        $tiempo_cambio_contraseña = 1;
        $color = 'primary';
        if ($user) {

            // Preferencias
            $preferencias = Cache::remember(
                'user_preferencias_' . $user->id,
                now()->addHours(12),
                function () use ($user) {

                    return UserPersonalizacion::where('user_id', $user->id)
                        ->first();
                }
            );

            // Sidebar
            $sidebarColor = $preferencias?->sidebar_color ?? 'primary';

            // Config
            $config = Cache::rememberForever(
                'config_credenciales',
                function () {

                    return ConfiguracionCredenciales::first();
                }
            );

            // Password
            $tiempo_cambio_contraseña = Cache::remember(
                'password_status_' . $user->id,
                now()->addHours(12),
                function () use ($user, $config) {

                    if (!$config) {
                        return 1;
                    }

                    if (!$user->usuario_fecha_ultimo_password) {
                        return 1;
                    }

                    $dias = Carbon::parse(
                        $user->usuario_fecha_ultimo_password
                    )->diffInDays(now());

                    return ($dias >= $config->conf_duracion_max)
                        ? 1
                        : 2;
                }
            );


            if (method_exists($user, 'preferences') && $user->preferences) {
                $color = $user->preferences->sidebar_color ?? 'primary';
            }
        }

        View::share([
            'preferencias' => $preferencias,
            'sidebarColor' => $sidebarColor,
            'tiempo_cambio_contraseña' => $tiempo_cambio_contraseña,
            'color' => $color
        ]);


        // limpiar temporales 1 vez por día
        if (!Cache::has('cleanup_import_temp')) {

            $path = storage_path('app/import_temp');

            if (File::exists($path)) {

                foreach (File::files($path) as $archivo) {

                    // 3 días
                    if (
                        now()->timestamp - $archivo->getMTime()
                        > (60 * 60 * 24 * 3)
                    ) {

                        File::delete($archivo->getPathname());
                    }
                }
            }

            Cache::put(
                'cleanup_import_temp',
                true,
                now()->addDay()
            );
        }

        return $next($request);
    }
}