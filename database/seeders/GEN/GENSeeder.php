<?php

namespace Database\Seeders\GEN;

use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;


class GENSeeder extends Seeder
{
    public function run(): void
    {



        //Contenido minimo para levantar sistema
        $this->call(UserSeeder::class);
        $this->call(RolesPermissionsSeeder::class);
        $this->call(CategoriaSeeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(ConfiguracionSeeder::class);
        $this->call(ConfCorreoSeeder::class);
        $this->call(SeccionesSeeder::class);
        $this->call(MenusSeeder::class);
        $this->call(ConfiguracionCredencialesSeeder::class);
        $this->call(GeneroSeeder::class);
        $this->call(TipoDocumentoSeeder::class);
        $this->call(FormulariosSeeder::class);
        $this->call(CamposSeeder::class);
        $this->call(TipoArchivosSeeder::class);
        $this->call(TipoImagenesSeeder::class);
        $this->call(TipoVideosSeeder::class);
        $this->call(OperacionesCampoSeeder::class);
        $this->call(TipoAccionSeeder::class);
        $this->call(EstadosActivoInactivoSeeder::class);
        $this->call(TiposPermisosSeeder::class);

        $this->call(class: WidgetsSeeder::class);


    }
}