<?php

namespace App\Repositories;

use App\Interfaces\FormularioInterface;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use App\Models\RespuestasForm;
use Illuminate\Http\Request;

class FormularioRepository implements FormularioInterface
{
    protected $model;

    public function __construct(Formulario $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $entity = $this->find($id);
        $entity->update($data);
        return $entity;
    }

    public function delete(int $id)
    {
        $entity = $this->find($id);
        return $entity->delete();
    }


    /* =============================================================
      FUNCIONES REUTILIZABLES
   ============================================================= */

    public function crearRespuesta($form)
    {
        return RespuestasForm::create([
            'form_id' => $form,
            'actor_id' => auth()->id() ?? null,
        ]);
    }

    public function guardarCampo($campo, $respuesta_id, Request $request, $form)
    {
        $tipo = strtolower($campo->campo_nombre);
        $name = $campo->nombre;

        if (in_array($tipo, ['imagen', 'video', 'archivo']) && $request->hasFile($name)) {
            $file = $request->file($name);
            $filename = uniqid($tipo . '_') . '.' . $file->getClientOriginalExtension();
            $path = match ($tipo) {
                'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                'video' => public_path("archivos/formulario_{$form}/videos"),
                'archivo' => public_path("archivos/formulario_{$form}/archivos"),
            };
            if (!file_exists($path))
                mkdir($path, 0777, true);
            $file->move($path, $filename);
            $this->guardarValorSimple($campo, $respuesta_id, $filename);
        } elseif ($request->has($name)) {
            $valor = $request->input($name);
            if (is_array($valor)) {
                foreach ($valor as $v)
                    $this->guardarValorSimple($campo, $respuesta_id, $v);
            } else {
                $this->guardarValorSimple($campo, $respuesta_id, $valor);
            }
        }
    }

    public function guardarArchivoGenerico($campo, $respuestaId, $form, $ruta)
    {
        // Ruta genérica que se puede cambiar según tipo
        $rutaGen = $ruta; // por ejemplo: 'archivos/default.jpg'
        RespuestasCampo::create([
            'respuesta_id' => $respuestaId,
            'cf_id' => $campo->id,
            'valor' => $rutaGen,
        ]);
    }
    // Función para guardar un valor simple
    public function guardarValorSimple($campo, $respuestaId, $valor)
    {
        if (is_array($valor)) {
            foreach ($valor as $v) {
                RespuestasCampo::create([
                    'respuesta_id' => $respuestaId,
                    'cf_id' => $campo->id,
                    'valor' => $v,
                ]);
            }
        } else {
            RespuestasCampo::create([
                'respuesta_id' => $respuestaId,
                'cf_id' => $campo->id,
                'valor' => $valor,
            ]);
        }
    }

    public function validarOpcionesCatalogo($campos, $request)
    {
        $errores = [];

        foreach ($campos as $campo) {
            $tipo = strtolower($campo->campo_nombre);
            $name = $campo->nombre;

            if (in_array($tipo, ['checkbox', 'radio', 'selector']) && $request->has($name)) {
                $valores = is_array($request->input($name)) ? $request->input($name) : [$request->input($name)];
                $opcionesValidas = $campo->opciones_catalogo->pluck('catalogo_codigo')->toArray();

                foreach ($valores as $v) {
                    if (!in_array($v, $opcionesValidas)) {
                        $errores[] = "El valor '$v' no es válido para el campo '{$campo->etiqueta}'.";
                    }
                }
            }
        }

        return $errores;
    }
}
