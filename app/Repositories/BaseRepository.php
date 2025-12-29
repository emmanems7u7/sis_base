<?php
namespace App\Repositories;

use HTMLPurifier;
use HTMLPurifier_Config;
use App\Models\ConfiguracionCredenciales;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
class BaseRepository
{
    protected $purifier;
    protected $configuracion;
    public function __construct()
    {
        // Configuración de HTMLPurifier
        $config = HTMLPurifier_Config::createDefault();
        $this->purifier = new HTMLPurifier($config);
        $this->configuracion = ConfiguracionCredenciales::first();
    }

    /**
     * Limpiar el contenido HTML
     *
     * @param string $content
     * @return string
     */
    protected function cleanHtml($content)
    {
        if (empty($content)) {
            return null;
        }
        return $this->purifier->purify($content);
    }

    protected function agregarSeederADatabaseSeeder(string $nombreClase, string $prefijo): void
    {
        $rutaDatabaseSeeder = database_path('seeders/DatabaseSeeder.php');
        if (!File::exists($rutaDatabaseSeeder)) {
            return;
        }

        $contenidoSeeder = File::get($rutaDatabaseSeeder);
        $fecha = date('d-m-Y'); // Ej: 26-12-2025
        $inicio = "/******************** Seeders creados automaticamente {$prefijo}{$fecha} ****************************/";
        $fin = "/********************  Fin Seeders creados automaticamente {$prefijo}{$fecha} ****************************/";
        $linea = "        \$this->call({$nombreClase}::class);";

        // Evitar duplicados globales
        if (Str::contains($contenidoSeeder, $linea)) {
            return;
        }

        // Detectar categoría según nombre de clase
        $categoria = 'OTROS';
        if (Str::contains($nombreClase, 'Seccion')) {
            $categoria = 'SECCION';
        } elseif (Str::contains($nombreClase, 'Menu')) {
            $categoria = 'MENU';
        } elseif (Str::contains($nombreClase, 'Permisos')) {
            $categoria = 'PERMISOS';
        }

        // Si ya existe bloque de la fecha
        if (Str::contains($contenidoSeeder, $inicio) && Str::contains($contenidoSeeder, $fin)) {
            $contenidoModificado = preg_replace_callback(
                "/(^[ \t]*" . preg_quote($inicio, '/') . ")(.*?)(^[ \t]*" . preg_quote($fin, '/') . ")/sm",
                function ($matches) use ($linea, $categoria) {
                    $bloque = $matches[2];
                    $iniCat = "        // {$categoria}";
                    $finCat = "        // FIN {$categoria}";

                    if (Str::contains($bloque, $iniCat) && Str::contains($bloque, $finCat)) {
                        return preg_replace_callback(
                            "/(" . preg_quote($iniCat, '/') . ".*?" . preg_quote($finCat, '/') . ")/s",
                            function ($sub) use ($linea, $finCat) {
                                if (Str::contains($sub[0], $linea))
                                    return $sub[0];
                                return str_replace($finCat, $linea . "\n\n" . $finCat, $sub[0]);
                            },
                            $matches[0],
                            1
                        );
                    }

                    // Crear bloque nuevo de la categoría si no existe
                    $nuevoSubbloque = "\n        // {$categoria}\n{$linea}\n\n        // FIN {$categoria}\n";
                    return str_replace($matches[3], $nuevoSubbloque . $matches[3], $matches[0]);
                },
                $contenidoSeeder,
                1
            );

            File::put($rutaDatabaseSeeder, $contenidoModificado);
        } else {
            // Crear bloque nuevo al final del método run()
            $bloque = "\n        {$inicio}\n        // {$categoria}\n{$linea}\n\n        // FIN {$categoria}\n        {$fin}\n";
            $contenidoModificado = preg_replace(
                '/(    \})/m',
                $bloque . "    }",
                $contenidoSeeder,
                1
            );

            File::put($rutaDatabaseSeeder, $contenidoModificado);
        }
    }
}
