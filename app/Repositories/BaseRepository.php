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

    protected function agregarSeederADatabaseSeeder(string $nombreClase): void
    {
        $rutaDatabaseSeeder = database_path('seeders/DatabaseSeeder.php');
        if (!File::exists($rutaDatabaseSeeder)) {
            return;
        }

        $contenidoSeeder = File::get($rutaDatabaseSeeder);

        $fecha = date('d-m-Y'); // ejemplo: 17-08-2025
        $inicio = `/******************** Seeders creados automaticamente {$fecha} ****************************/`;
        $fin = `/********************  Fin Seeders creados automaticamente {$fecha} ****************************/`;
        $linea = "        \$this->call({$nombreClase}::class);";

        // Evitar duplicados globales
        if (Str::contains($contenidoSeeder, $linea)) {
            return;
        }

        // Detectar categoría según nombre de clase
        $categoria = null;
        if (Str::contains($nombreClase, 'Seccion')) {
            $categoria = 'SECCION';
        } elseif (Str::contains($nombreClase, 'Menu')) {
            $categoria = 'MENU';
        } elseif (Str::contains($nombreClase, 'Permisos')) {
            $categoria = 'PERMISOS';
        } else {
            $categoria = 'OTROS';
        }

        // Si ya existe bloque de la fecha
        if (Str::contains($contenidoSeeder, $inicio) && Str::contains($contenidoSeeder, $fin)) {
            $contenidoModificado = preg_replace_callback(
                "/(^[ \t]*" . preg_quote($inicio, '/') . ")(.*?)(^[ \t]*" . preg_quote($fin, '/') . ")/sm",
                function ($matches) use ($linea, $categoria) {
                    $bloque = $matches[2];

                    // Buscar subbloque de la categoría
                    $iniCat = "        // {$categoria}";
                    $finCat = "        // FIN {$categoria}";

                    // Si existe categoría, insertar antes de su FIN
                    if (Str::contains($bloque, $iniCat) && Str::contains($bloque, $finCat)) {
                        return preg_replace_callback(
                            "/(" . preg_quote($iniCat, '/') . ".*?" . preg_quote($finCat, '/') . ")/s",
                            function ($sub) use ($linea, $finCat) {
                                if (Str::contains($sub[0], $linea)) {
                                    return $sub[0]; // evitar duplicados
                                }
                                return str_replace($finCat, $linea . "\n\n" . $finCat, $sub[0]);
                            },
                            $matches[0],
                            1
                        );
                    }

                    // Si no existe, crear el bloque de la categoría en orden
                    $orden = ['SECCION', 'MENU', 'PERMISOS', 'OTROS'];
                    $nuevoSubbloque = "\n        // {$categoria}\n{$linea}\n\n        // FIN {$categoria}\n";

                    // Insertar en la posición correcta según dependencias
                    foreach ($orden as $cat) {
                        $finCatExistente = "        // FIN {$cat}";
                        if ($cat === $categoria) {
                            // Si es la misma categoría y no existe aún → insertamos justo antes del FIN global
                            return str_replace($matches[3], $nuevoSubbloque . $matches[3], $matches[0]);
                        } elseif (Str::contains($bloque, $finCatExistente)) {
                            // Si ya existe un bloque anterior, insertamos después de él
                            return str_replace($finCatExistente, $finCatExistente . $nuevoSubbloque, $matches[0]);
                        }
                    }

                    // Si no encontró nada, lo pone antes del FIN global
                    return str_replace($matches[3], $nuevoSubbloque . $matches[3], $matches[0]);
                },
                $contenidoSeeder,
                1,
                $reemplazos
            );

            if ($reemplazos > 0) {
                File::put($rutaDatabaseSeeder, $contenidoModificado);
            }
        } else {
            // Crear bloque nuevo al final del método run()
            $bloque = "\n        {$inicio}\n"
                . "        // {$categoria}\n{$linea}\n\n        // FIN {$categoria}\n"
                . "        {$fin}\n";

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
