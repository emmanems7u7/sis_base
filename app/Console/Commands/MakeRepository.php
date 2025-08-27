<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name}';
    protected $description = 'Crea una Interface y un Repository y lo registra en AppServiceProvider';

    public function handle()
    {
        $name = $this->argument('name');

        $interfacePath = app_path("Interfaces/{$name}Interface.php");
        $repositoryPath = app_path("Repositories/{$name}Repository.php");
        $providerPath = app_path("Providers/AppServiceProvider.php");

        // Crear carpetas si no existen
        if (!file_exists(app_path('Interfaces')))
            mkdir(app_path('Interfaces'), 0777, true);
        if (!file_exists(app_path('Repositories')))
            mkdir(app_path('Repositories'), 0777, true);

        // Crear Interface
        if (!file_exists($interfacePath)) {
            file_put_contents($interfacePath, $this->generateInterface($name));
            $this->info("Interface creada: {$interfacePath}");
        } else {
            $this->error("La Interface ya existe: {$interfacePath}");
        }

        // Crear Repository
        if (!file_exists($repositoryPath)) {
            file_put_contents($repositoryPath, $this->generateRepository($name));
            $this->info("Repository creada: {$repositoryPath}");
        } else {
            $this->error("El Repository ya existe: {$repositoryPath}");
        }

        // Agregar bind a AppServiceProvider
        $this->addBindToProvider($providerPath, $name);
    }

    protected function generateInterface($name)
    {
        return "<?php

namespace App\Interfaces;

interface {$name}Interface
{
    public function all();
    public function find(int \$id);
    public function create(array \$data);
    public function update(int \$id, array \$data);
    public function delete(int \$id);
}
";
    }

    protected function generateRepository($name)
    {
        return "<?php

namespace App\Repositories;

use App\Interfaces\\{$name}Interface;
use App\Models\\{$name};

class {$name}Repository implements {$name}Interface
{
    protected \$model;

    public function __construct({$name} \$model)
    {
        \$this->model = \$model;
    }

    public function all()
    {
        return \$this->model->all();
    }

    public function find(int \$id)
    {
        return \$this->model->findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return \$this->model->create(\$data);
    }

    public function update(int \$id, array \$data)
    {
        \$entity = \$this->find(\$id);
        \$entity->update(\$data);
        return \$entity;
    }

    public function delete(int \$id)
    {
        \$entity = \$this->find(\$id);
        return \$entity->delete();
    }
}
";
    }

    protected function addBindToProvider($providerPath, $name)
    {
        $content = file_get_contents($providerPath);

        $bindLine = "        \$this->app->bind({$name}Interface::class, {$name}Repository::class);";

        if (strpos($content, $bindLine) === false) {

            // Buscar cierre de register() y añadir antes de }
            $content = preg_replace(
                '/(public function register\(\): void\s*\{\s*)(.*?)(\s*\})/s',
                "$1$2\n$bindLine\n$3",
                $content
            );

            // Añadir use statements
            $uses = "use App\Interfaces\\{$name}Interface;\nuse App\Repositories\\{$name}Repository;";
            if (strpos($content, $uses) === false) {
                $content = preg_replace('/(<\?php\s+namespace App\\\Providers;)/', "$1\n$uses", $content);
            }

            file_put_contents($providerPath, $content);
            $this->info("Bind agregado en AppServiceProvider: {$name}Interface -> {$name}Repository");
        } else {
            $this->info("Bind ya existe en AppServiceProvider.");
        }
    }
}
