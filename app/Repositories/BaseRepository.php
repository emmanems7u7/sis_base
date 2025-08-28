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

    protected function agregarSeederADatabaseSeeder(string $nombreClase, string $bloqueEtiqueta): void
    {
        $rutaDatabaseSeeder = database_path('seeders/DatabaseSeeder.php');
        if (!File::exists($rutaDatabaseSeeder))
            return;

        $contenidoSeeder = File::get($rutaDatabaseSeeder);

        if (Str::contains($contenidoSeeder, "\$this->call({$nombreClase}::class)"))
            return;

        $linea = "        \$this->call({$nombreClase}::class);";
        $inicio = "//INICIO {$bloqueEtiqueta}";
        $fin = "//FIN {$bloqueEtiqueta}";

        // Permite espacios/tabs antes de los comentarios
        $contenidoModificado = preg_replace_callback(
            "/(^[ \t]*" . preg_quote($inicio, '/') . ")(.*?)(^[ \t]*" . preg_quote($fin, '/') . ")/sm",
            function ($matches) use ($linea) {
                $bloque = rtrim($matches[2]);

                // Evitar duplicado si ya existe
                if (Str::contains($bloque, $linea)) {
                    return "{$matches[1]}{$matches[2]}{$matches[3]}";
                }

                // Insertar la línea justo antes del FIN
                $nuevoBloque = $bloque . "\n" . $linea . "\n";

                return "{$matches[1]}\n{$nuevoBloque}{$matches[3]}";
            },
            $contenidoSeeder,
            1,
            $reemplazos
        );

        if ($reemplazos > 0) {
            File::put($rutaDatabaseSeeder, $contenidoModificado);
        }
    }
}
