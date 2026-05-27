<?php

namespace App\Repositories;

use App\Interfaces\CamposFormInterface;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CamposFormRepository implements CamposFormInterface
{
    protected $CatalogoRepository;

    public function __construct(CatalogoInterface $catalogoInterface)
    {
        $this->CatalogoRepository = $catalogoInterface;

    }


    public function obtenerOpcionesCompletas($campos)
    {
        $resultado = collect();

        foreach ($campos as $campo) {

            if ($campo->categoria_id) {
                // Trae todos los catálogos activos de la categoría
                $campo->opciones_catalogo = $this->CatalogoRepository
                    ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
            } elseif ($campo->form_ref_id) {


                // Campo que referencia a otro formulario
                $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                    ->orderBy('posicion', 'asc')
                    ->first();

                if ($campoReferencia) {


                    $formulario = Formulario::find($campo->form_ref_id);
                    $configConcatenado = $formulario->config['configuracion_concatenado'] ?? null;

                    $campo->opciones_catalogo = $campo->opcionesFormularioQuery()
                        ->with('camposRespuestas')
                        ->get()
                        ->map(function ($respuesta) use ($campoReferencia, $configConcatenado) {

                            $camposRespuesta = $respuesta?->camposRespuestas;


                            if (!$configConcatenado || !$camposRespuesta) {
                                $valorCampo = optional($camposRespuesta
                                    ->firstWhere('cf_id', $campoReferencia->id))
                                    ->valor ?? $respuesta->valor;
                            } else {
                                $valoresPorId = $camposRespuesta->pluck('valor', 'cf_id')->toArray();
                                $estructura = $configConcatenado['estructura'];

                                $valorCampo = preg_replace_callback(
                                    '/\d+/',
                                    fn($matches) => $valoresPorId[$matches[0]] ?? $matches[0],
                                    $estructura
                                );
                            }

                            return (object) [
                                'catalogo_codigo' => $respuesta->id,
                                'catalogo_descripcion' => $valorCampo ?? 'Sin nombre',
                            ];
                        });
                } else {
                    $campo->opciones_catalogo = collect();
                }
            } else {
                $campo->opciones_catalogo = collect();
            }

            $resultado->push($campo);
        }

        return $resultado;
    }

    public function CrearCampoForm($request, $formulario)
    {
        $posicion = CamposForm::where('form_id', $request->form_id)->max('posicion') + 1;

        $campo = CamposForm::create([
            'form_id' => $formulario->id,
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'posicion' => $posicion,
            'config' => $request->config ?? [],
            'form_ref_id' => $request->formulario_id ?: null,
        ]);

        return $campo;
    }

    public function EditarCampoForm($request, $campo)
    {
        $campo->update([
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'config' => $request->config ?? $campo->config,
            'form_ref_id' => $request->formulario_id ?: null,
        ]);

        return $campo;
    }

    public function CamposFormCat($campos, $limit = 20, $offset = 0)
    {
        $resultado = collect();

        foreach ($campos as $campo) {


            $campo = $this->ProcesarCampo($campo, $limit, $offset);

            $resultado->push($campo);
        }
        return $resultado;
    }
    public function ProcesarCampo($campo, $limit = 20, $offset = 0)
    {
        if ($campo->categoria_id) {


            $campo->opciones_catalogo = $this->CatalogoRepository
                ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true, $limit, $offset);


        } elseif ($campo->form_ref_id) {
            $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                ->orderBy('posicion', 'asc')
                ->first();

            if ($campoReferencia) {

                $formulario = Formulario::find($campo->form_ref_id);
                $configConcatenado = $formulario->config['configuracion_concatenado'] ?? null;

                $campo->opciones_catalogo = $campo->opcionesFormularioQuery()
                    ->offset($offset)
                    ->limit($limit)
                    ->get()
                    ->map(function ($respuesta) use ($configConcatenado, $campoReferencia) {

                        $respuestaReferencia = RespuestasCampo::find($respuesta->id);
                        $camposRespuesta = $respuestaReferencia?->camposRespuestas;

                        if (!$configConcatenado || !$camposRespuesta) {
                            $valorCampo = optional($respuesta->camposRespuestas
                                ->where('cf_id', $campoReferencia->id)
                                ->first())->valor ?? $respuesta->valor;
                        } else {

                            $valoresPorId = $camposRespuesta->pluck('valor', 'cf_id')->toArray();
                            $estructura = $configConcatenado['estructura'];

                            // Reemplazamos cada cf_id por su valor
                            $valorCampo = preg_replace_callback(
                                '/\d+/',
                                fn($matches) => $valoresPorId[$matches[0]] ?? $matches[0],
                                $estructura
                            );
                        }

                        return (object) [
                            'catalogo_codigo' => $respuesta->id,
                            'resp_valor' => $respuesta->valor,
                            'catalogo_descripcion' => $valorCampo ?? 'Sin nombre',
                        ];
                    });

            } else {
                $campo->opciones_catalogo = collect();
            }

        } else {
            $campo->opciones_catalogo = collect();
        }
        return $campo;
    }

    public function guardarCampo($campo, $respuesta_id, $datosFormulario, $form, $prefix = null)
    {
        $tipo = strtolower($campo->campo_nombre);
        $name = $campo->nombre;

        $inputKey = $prefix
            ? "{$prefix}[{$name}]"
            : $name;

        $valor = $datosFormulario[$inputKey] ?? null;

        if ($valor === null) {
            return;
        }

        // =========================================
        // ARCHIVOS
        // =========================================
        if (
            in_array($tipo, ['imagen', 'video', 'archivo'])
            && $valor instanceof \Illuminate\Http\UploadedFile
        ) {

            $filename = uniqid($tipo . '_') . '.' . $valor->getClientOriginalExtension();

            $path = match ($tipo) {
                'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                'video' => public_path("archivos/formulario_{$form}/videos"),
                'archivo' => public_path("archivos/formulario_{$form}/archivos"),
            };

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $valor->move($path, $filename);

            $this->guardarValorSimple($campo, $respuesta_id, $filename);

            return;
        }

        // =========================================
        // ARRAYS (checkbox multiple)
        // =========================================
        if (is_array($valor)) {

            foreach ($valor as $v) {
                $this->guardarValorSimple($campo, $respuesta_id, $v);
            }

            return;
        }

        // =========================================
        // SIMPLE
        // =========================================
        $this->guardarValorSimple($campo, $respuesta_id, $valor);
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


    public function actualizarCampo($campo, $respuesta_id, $datosFormulario, $form, $prefix = null)
    {
        $tipo = strtolower($campo->campo_nombre);

        $inputKey = $prefix
            ? "{$prefix}[{$campo->nombre}]"
            : $campo->nombre;

        $valor = $datosFormulario[$inputKey] ?? null;

        if ($valor === null) {
            return;
        }

        if (
            in_array($tipo, ['imagen', 'video', 'archivo'])
            && $valor instanceof \Illuminate\Http\UploadedFile
        ) {

            $filename = uniqid($tipo . '_') . '.' . $valor->getClientOriginalExtension();

            $path = match ($tipo) {
                'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                'video' => public_path("archivos/formulario_{$form}/videos"),
                'archivo' => public_path("archivos/formulario_{$form}/archivos"),
            };

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $valor->move($path, $filename);

            // actualizar valor archivo
            $this->actualizarValorSimple(
                $campo,
                $respuesta_id,
                $filename
            );

            return;
        }


        $this->actualizarValorSimple(
            $campo,
            $respuesta_id,
            $valor
        );
    }

    public function actualizarValorSimple($campo, $respuestaId, $valor)
    {
        if (is_array($valor)) {

            $registros = RespuestasCampo::where('respuesta_id', $respuestaId)
                ->where('cf_id', $campo->id)
                ->get();

            foreach ($valor as $index => $v) {

                // actualizar existente
                if (isset($registros[$index])) {

                    $registros[$index]->update([
                        'valor' => $v,
                    ]);

                } else {

                    // crear solo si falta
                    RespuestasCampo::create([
                        'respuesta_id' => $respuestaId,
                        'cf_id' => $campo->id,
                        'valor' => $v,
                    ]);
                }
            }

        } else {

            $registro = RespuestasCampo::where('respuesta_id', $respuestaId)
                ->where('cf_id', $campo->id)
                ->first();

            if ($registro) {

                $registro->update([
                    'valor' => $valor,
                ]);

            } else {

                RespuestasCampo::create([
                    'respuesta_id' => $respuestaId,
                    'cf_id' => $campo->id,
                    'valor' => $valor,
                ]);
            }
        }
    }

    public function GetCamposByForm($form_id)
    {

        return CamposForm::where('form_id', $form_id)->get();
    }
    public function GetCampo($campo_id)
    {
        return CamposForm::find($campo_id);
    }

    public function GetCampoOrderByPosicion($form_id)
    {
        return CamposForm::where('form_id', $form_id)->orderBy('posicion')->get();
    }
    public function GetCampoOrderByPosicionId($campo_id)
    {
        return CamposForm::where('id', $campo_id)->orderBy('posicion')->get();
    }

}
