<?php
echo "<pre>";

try {
    // Ruta a la raíz del proyecto (sube este archivo a public/)
    $root = realpath(__DIR__ . '/..');

    // Cargar autoload y el framework
    require $root . '/vendor/autoload.php';

    $app = require_once $root . '/bootstrap/app.php';

    // Crear kernel de consola
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    // Ejecutar comandos de limpieza
    $kernel->call('config:clear');
    $kernel->call('cache:clear');
    $kernel->call('route:clear');
    $kernel->call('view:clear');

    echo $kernel->output();
    echo "\n✅ Caché limpiada con éxito.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
