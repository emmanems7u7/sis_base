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

    protected function agregarSeederADatabaseSeeder(string $nombreClase, string $stage, string $tipo): void
    {
        $rutaPadre = database_path("seeders/{$stage}/{$tipo}/{$tipo}Seeder.php");

        // Crear archivo padre si no existe
        if (!File::exists($rutaPadre)) {
            $contenidoBase = <<<PHP
    <?php
    
    namespace Database\Seeders\\{$stage}\\{$tipo};
    
    use Illuminate\Database\Seeder;
    
    class {$tipo}Seeder extends Seeder
    {
        public function run(): void
        {
            // SEEDERS GENERADOS
        }
    }
    PHP;
            File::ensureDirectoryExists(dirname($rutaPadre));
            File::put($rutaPadre, $contenidoBase);
        }

        $contenido = File::get($rutaPadre);

        $lineaNueva = "        \$this->call({$nombreClase}::class);";

        // Extraer todas las líneas actuales de seeders
        preg_match('/(\/\/ SEEDERS GENERADOS)(.*?)(\n\s*\})/s', $contenido, $matches);
        $bloque = $matches[2] ?? '';

        $lineas = [];

        // Extraer cada línea de $this->call existente
        if (preg_match_all('/\$this->call\((.*?)::class\);/', $bloque, $m)) {
            $lineas = array_map(function ($cls) {
                return "        \$this->call({$cls}::class);";
            }, $m[1]);
        }

        // Agregar la nueva si no existe
        if (!in_array($lineaNueva, $lineas)) {
            $lineas[] = $lineaNueva;
        }

        // Ordenar alfabéticamente (YYYYMMDD funciona perfecto)
        sort($lineas);

        // Reconstruir el bloque
        $nuevoBloque = "// SEEDERS GENERADOS\n" . implode("\n", $lineas);

        // Reemplazar el bloque original dentro del método run()
        $contenido = preg_replace(
            '/(\/\/ SEEDERS GENERADOS)(.*?)(\n\s*\})/s',
            $nuevoBloque . "$3",
            $contenido,
            1
        );

        File::put($rutaPadre, $contenido);
    }

    /**
     * Elimina la línea $this->call(...) del seeder padre.
     */
    public function eliminarLlamadaDeSeederPadre(string $stage, string $tipo, string $nombreSeeder): void
    {
        $rutaPadre = database_path("seeders/{$stage}/{$tipo}/{$tipo}Seeder.php");
        if (!File::exists($rutaPadre)) {
            return;
        }

        $contenido = File::get($rutaPadre);
        $linea = "        \$this->call({$nombreSeeder}::class);";

        if (Str::contains($contenido, $linea)) {
            $contenido = str_replace($linea, '', $contenido);

            // Limpiar líneas vacías dobles
            $contenido = preg_replace("/\n{2,}/", "\n", $contenido);

            File::put($rutaPadre, $contenido);
        }
    }
}
