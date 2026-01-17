<?php
namespace App\Repositories;

use App\Interfaces\CorreoInterface;
use \App\Models\ConfCorreo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\PlantillaCorreo;

class CorreoRepository extends BaseRepository implements CorreoInterface
{
    private $rutaPlantillas = 'plantillas_correos';
    public function __construct()
    {
        parent::__construct();

    }
    public function EditarPlantillaCorreo($request, $email)
    {
        $email->nombre = $request->input('nombre_plantilla');
        $email->asunto = $request->input('asunto_plantilla');
        $email->contenido = $request->input('contenido');
        $email->save();
    }
    public function EditarConfCorreo($correoId, $request)
    {
        $confCorreo = ConfCorreo::updateOrCreate(

            ['id' => $correoId],
            [
                'conf_protocol' => $this->cleanHtml($request['conf_correo_protocol']),
                'conf_smtp_host' => $this->cleanHtml($request['conf_smtp_host']),
                'conf_smtp_port' => $this->cleanHtml($request['conf_smtp_port']),
                'conf_smtp_user' => $this->cleanHtml($request['conf_smtp_user']),
                'conf_smtp_pass' => $this->cleanHtml($request['conf_smtp_pass']),
                'conf_mailtype' => $this->cleanHtml($request['conf_mailtype']),
                'conf_charset' => $this->cleanHtml($request['conf_charset']),
                'conf_in_background' => $request['conf_in_background'],
                'accion_usuario' => Auth::user()->name,
            ]
        );

        return $confCorreo;
    }

    public function CrearPlantilla($request)
    {

        // Sanitizar HTML básico
        $contenido = strip_tags(
            $request->contenido,
            '<p><div><span>
             <h1><h2><h3><h4><h5><h6>
             <strong><em>
             <ul><ol><li>
             <a><img>
             <br><hr>
             <style>
             <table><thead><tbody><tfoot><tr><th><td><caption><colgroup><col>'
        );
        // Crear carpeta si no existe
        if (!File::exists(public_path($this->rutaPlantillas))) {
            File::makeDirectory(public_path($this->rutaPlantillas), 0755, true);
        }

        // Nombre de archivo único
        $archivoNombre = Str::slug($request->nombre) . '-' . time() . '.blade.php';
        File::put(public_path($this->rutaPlantillas . '/' . $archivoNombre), $contenido);

        $plantilla = PlantillaCorreo::create([
            'nombre' => $request->nombre,
            'archivo' => $archivoNombre,
            'estado' => $request->estado,
        ]);
        return $plantilla;
    }

    public function EditarPlantilla($request, $plantilla)
    {


        // Sanitizar HTML básico
        $contenido = strip_tags(
            $request->contenido,
            '<p><div><span>
             <h1><h2><h3><h4><h5><h6>
             <strong><em>
             <ul><ol><li>
             <a><img>
             <br><hr>
             <style>
             <table><thead><tbody><tfoot><tr><th><td><caption><colgroup><col>'
        );
        // Reescribir archivo
        $archivoNombre = $plantilla->archivo ?? Str::slug($request->nombre) . '-' . time() . '.blade.php';
        File::put(public_path($this->rutaPlantillas . '/' . $archivoNombre), $contenido);

        $plantilla->update([
            'nombre' => $request->nombre,
            'archivo' => $archivoNombre,
            'estado' => $request->estado,
        ]);
        return $plantilla;
    }

    public function EliminarPlantilla($plantilla)
    {
        if (File::exists(public_path($this->rutaPlantillas . '/' . $plantilla->archivo))) {
            File::delete(public_path($this->rutaPlantillas . '/' . $plantilla->archivo));
        }

    }
}
