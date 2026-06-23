<?php

namespace App\Repositories;

use App\Interfaces\FormularioInterface;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\RespuestasCampoInterface;
use App\Models\CamposForm;
use App\Models\Catalogo;
use App\Models\FormLogicRule;
use App\Models\Formulario;
use App\Models\ModuloFormularioParalelo;
use App\Models\RespuestasCampo;
use App\Models\RespuestasForm;
use App\Models\RespuestasGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;


class FormularioRepository implements FormularioInterface
{
    protected $model;
    protected $CatalogoRepository;


    public function __construct(Formulario $model, CatalogoInterface $catalogoInterface)
    {
        $this->model = $model;
        $this->CatalogoRepository = $catalogoInterface;


    }


    public function CrearFormulario($request)
    {
        $formulario = Formulario::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
            'estado' => $request->estado,
            'campos_columnas' => $request->campos_columnas
        ]);

        $this->ActualizarConfig($formulario, $request);


        return $formulario;
    }

    function ActualizarConfig($formulario, Request $request)
    {
        $config = $formulario->config ?? [];

        if (($config['crear_permisos'] ?? false) === true) {
            $config['crear_permisos'] = true;
        } else {
            $config['crear_permisos'] =
                $request->has('crear_permisos') &&
                $request->crear_permisos === 'on';
        }

        $config['registro_multiple'] =
            $request->has('registro_multiple') &&
            $request->registro_multiple === 'on';

        $formulario->update([
            'config' => $config
        ]);
    }
    public function EditarFormulario($request, $formulario)
    {
        $formulario->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
            'estado' => $request->estado,
            'campos_columnas' => $request->campos_columnas
        ]);
        $this->ActualizarConfig($formulario, $request);

    }

    public function CrearRespuestaGrupo()
    {
        $codigo = random_int(100000, 999999);

        $grupo = RespuestasGrupo::create([
            'actor_id' => auth()->id() ?? null,
            'codigo' => $codigo,
        ]);
        return $grupo;
    }
    public function crearRespuesta($form)
    {
        return RespuestasForm::create([
            'form_id' => $form,
            'actor_id' => auth()->id() ?? null,
        ]);
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

    public function convertirValorParaFiltro($campo, $valorUsuario)
    {
        // Si es categoría
        if ($campo->categoria_id) {
            // Buscamos el catalogo correspondiente al valor ingresado
            $catalogo = $this->CatalogoRepository
                ->buscarPorDescripcion($campo->categoria_id, $valorUsuario);

            return $catalogo ? $catalogo->catalogo_codigo : null;

            // Si es formulario de referencia
        } elseif ($campo->form_ref_id) {


            // Obtenemos el primer campo del formulario de referencia
            $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                ->orderBy('posicion', 'asc')
                ->first();


            if (!$campoReferencia)
                return null;

            // Buscamos la respuesta en el formulario de referencia donde el primer campo coincida con el valor
            $respuesta = RespuestasForm::where('form_id', $campo->form_ref_id)
                ->whereHas('camposRespuestas', function ($q) use ($campoReferencia, $valorUsuario) {
                    $q->where('cf_id', $campoReferencia->id)
                        ->where('valor', 'like', "%{$valorUsuario}%");
                })
                ->first();

            return $respuesta ? $respuesta->id : null;
        }

        // Para otros tipos (text, number, textarea, etc.) devolvemos el valor directo
        return $valorUsuario;
    }


    /**
     * Obtiene el valor real para reemplazo o filtrado según el tipo de campo.
     *
     * @param CamposForm $campo
     * @param mixed $valorUsuario
     * @return mixed
     */
    public function obtenerValorReal(CamposForm $campo, $valorUsuario)
    {

        if (!empty($campo->categoria_id)) {
            $catalogo = $this->CatalogoRepository->buscarPorCodigo($campo->categoria_id, $valorUsuario);
            return $catalogo ? $catalogo->catalogo_descripcion : null;
        }
        if (!empty($campo->form_ref_id)) {

            // Si no es numérico, ya es el valor real
            if (!is_numeric($valorUsuario)) {
                return $valorUsuario;
            }

            $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)->orderBy('posicion', 'asc')->first();

            if (!$campoReferencia) {
                return null;
            }

            $respuestaCampo = RespuestasCampo::where('respuesta_id', $valorUsuario)
                ->where('cf_id', $campoReferencia->id)
                ->first();

            return optional($respuestaCampo)->valor;
        }

        return $valorUsuario;
    }


    public function procesarFormularioConFiltros($formulario, Request $request, $pageName = null)
    {
        $camposPorNombre = $formulario->campos->keyBy('id');

        $query = RespuestasForm::where('form_id', $formulario->id)
            ->with('camposRespuestas.campo', 'actor', 'grupos');

        $query = $this->aplicarFiltrosFormulario(
            $query,
            $formulario,
            $request
        );

        $respuestas = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], $pageName ?? 'page')
            ->withQueryString();

        // Cargar solo campos visibles en listado
        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->whereJsonContains('config->visible_listado', true)
                    ->orderBy('posicion');
            }
        ])->findOrFail($formulario->id);

        $formIds = [];
        $respuestaIds = [];
        $catalogosNecesarios = [];

        foreach ($respuestas as $respuesta) {

            foreach ($respuesta->camposRespuestas as $campoResp) {

                if ($campoResp->campo?->form_ref_id) {

                    $formIds[] = $campoResp->campo->form_ref_id;
                    $respuestaIds[] = $campoResp->valor;
                }

                if ($campoResp->campo?->categoria_id) {

                    $catalogosNecesarios[] = [
                        'categoria_id' => $campoResp->campo->categoria_id,
                        'codigo' => $campoResp->valor,
                    ];
                }
            }
        }

        $formulariosMap = Formulario::whereIn('id', array_unique($formIds))->get()->keyBy('id');

        $respuestasMap = RespuestasForm::with('camposRespuestas')
            ->whereIn('id', array_unique($respuestaIds))
            ->get()
            ->keyBy('id');


        $catalogosMap = Catalogo::query()
            ->where(function ($q) use ($catalogosNecesarios) {

                foreach ($catalogosNecesarios as $item) {

                    $q->orWhere(function ($sub) use ($item) {

                        $sub->where('categoria_id', $item['categoria_id'])
                            ->where('catalogo_codigo', $item['codigo']);
                    });
                }
            })
            ->get()
            ->keyBy(function ($item) {

                return $item->categoria_id . '_' . $item->catalogo_codigo;
            });

        foreach ($respuestas as $respuesta) {

            $respuesta->grupo = $respuesta->grupos->isNotEmpty() ? 1 : 0;

            foreach ($respuesta->camposRespuestas as $campoResp) {

                $campoResp->valor = $this->resolverValor(
                    $campoResp,
                    null,
                    $formulariosMap,
                    $respuestasMap,
                    $catalogosMap
                );
            }
        }

        /*
                // DEBUG para verificar
                foreach ($respuestas as $respuesta) {
                    dump([
                        'respuesta_id' => $respuesta->id,
                        'campos' => $respuesta->camposRespuestas->map(fn($c) => [
                            'cf_id' => $c->cf_id,
                            'valor' => $c->valor,
                            'campo_nombre' => $c->campo->campo_nombre ?? null,
                            'form_ref_id' => $c->campo->form_ref_id ?? null,
                            'categoria_id' => $c->campo->categoria_id ?? null,

                        ]),
                    ]);
                }



                dd($respuestas->items());*/
        return [
            'formulario' => $formulario,
            'respuestas' => $respuestas,
        ];
    }
    public function resolverValor(
        $campoRespOrCampo,
        $valor = null,
        $formulariosMap = [],
        $respuestasMap = [],
        $catalogosMap = []
    ) {

        if ($valor === null) {
            $campoResp = $campoRespOrCampo;
            $campo = $campoResp->campo;
            $valor = $campoResp->valor;
        } else {

            $campo = $campoRespOrCampo;
        }

        if ($campo && $campo->form_ref_id) {

            $formulario = $formulariosMap[$campo->form_ref_id] ?? null;
            $respuestaReferencia = $respuestasMap[$valor] ?? null;

            $camposRespuesta = $respuestaReferencia?->camposRespuestas;

            $configConcatenado = $formulario->config['configuracion_concatenado'] ?? null;

            if (!$configConcatenado || !$camposRespuesta) {
                return $respuestaReferencia?->camposRespuestas?->first()?->valor ?? $valor;
            }

            $valoresPorId = $camposRespuesta->pluck('valor', 'cf_id')->toArray();

            $estructura = $configConcatenado['estructura'];

            $resultado = preg_replace_callback(
                '/\d+/', // coincidimos números que son cf_id
                function ($matches) use ($valoresPorId) {
                    $id = $matches[0];
                    return $valoresPorId[$id] ?? $id; // si no existe, dejamos el id
                },
                $estructura
            );

            return $resultado;



        }


        if ($campo && $campo->categoria_id) {

            $key = $campo->categoria_id . '_' . $valor;

            $catalogo = $catalogosMap[$key] ?? null;

            return $catalogo?->catalogo_descripcion ?? $valor;
        }


        return $valor;
    }


    public function aplicarFiltrosFormulario($query, $formulario, Request $request)
    {
        $camposPorNombre = $formulario->campos->keyBy('id');

        $inputs = collect($request->all())
            ->except(['_token', 'page'])
            ->filter(fn($v) => $v !== null && $v !== '');

        foreach ($inputs as $nombreCampo => $valorEnviado) {

            if (!$camposPorNombre->has($nombreCampo)) {
                continue;
            }

            $campo = $camposPorNombre->get($nombreCampo);

            $query->whereHas('camposRespuestas', function ($q) use ($campo, $valorEnviado) {

                $q->where('cf_id', $campo->id);

                switch ($campo->campo_nombre) {

                    case 'text':
                    case 'textarea':
                    case 'email':
                    case 'password':
                    case 'enlace':
                        $q->where('valor', 'like', "%{$valorEnviado}%");
                        break;

                    case 'checkbox':

                        foreach ($valorEnviado as $valor) {

                            $q->whereExists(function ($sub) use ($campo, $valor) {

                                $sub->select(DB::raw(1))
                                    ->from('respuestas_campos as cr2')
                                    ->whereColumn('cr2.respuesta_id', 'respuestas_campos.respuesta_id')
                                    ->where('cr2.cf_id', $campo->id)
                                    ->where('cr2.valor', $valor);
                            });
                        }

                        break;

                    default:
                        $q->where('valor', $valorEnviado);
                }
            });
        }

        return $query;
    }

    public function generar_informacion_export($respuestas, $formulario)
    {

        $datos = [];
        foreach ($respuestas as $respuesta) {
            $fila = [];
            //$fila['id'] = $respuesta->id;

            foreach ($formulario->campos->sortBy('posicion') as $campo) {
                $valores = $respuesta->camposRespuestas
                    ->where('cf_id', $campo->id)
                    ->pluck('valor')
                    ->toArray();

                $tipoCampo = strtolower($campo->campo_nombre);
                $display = [];

                foreach ($valores as $v) {
                    switch ($tipoCampo) {
                        case 'checkbox':
                        case 'radio':
                        case 'selector':
                            $desc = $campo->opciones_catalogo->where('catalogo_codigo', $v)->first()?->catalogo_descripcion;
                            $display[] = $desc ?? $v;
                            break;

                        case 'imagen':
                            $path = public_path("archivos/formulario_{$formulario->id}/imagenes/{$v}");
                            if (file_exists($path)) {
                                $base64 = base64_encode(file_get_contents($path));
                                $type = mime_content_type($path);
                                $display[] = "<img src='data:{$type};base64,{$base64}' style='max-width:80px; max-height:80px;' />";
                            }
                            break;

                        case 'video':
                        case 'archivo':
                            $display[] = $v; // Solo mostrar nombre
                            break;

                        case 'fecha':
                            $display[] = Carbon::parse($v)->format('d/m/Y');
                            break;

                        default:
                            $display[] = $v; // Text, Number, Textarea, Email, Password, Color, Hora, Enlace
                    }
                }

                $fila[$campo->etiqueta] = implode(' ', $display);
            }
            $fila['Actor'] = $respuesta->actor->name ?? 'Anónimo';
            $fila['Registrado'] = $respuesta->created_at->format('d/m/Y H:i');

            $datos[] = $fila;



        }
        return $datos;

    }
    public function procesarCamposRespuesta($respuesta, $formulario)
    {
        $resultado = [];

        foreach ($formulario->campos->sortBy('posicion') as $campo) {
            $valores = $respuesta->camposRespuestas
                ->where('cf_id', $campo->id)
                ->pluck('valor')
                ->toArray();

            $displayValores = [];
            foreach ($valores as $v) {
                $valorResuelto = $this->obtenerValorReal($campo, $v);

                $tipoCampo = strtolower($campo->campo_nombre);
                switch ($tipoCampo) {
                    case 'imagen':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/imagenes/{$valorResuelto}");
                        break;
                    case 'video':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/videos/{$valorResuelto}");
                        break;
                    case 'archivo':
                        $displayValores[] = asset("archivos/formulario_{$formulario->id}/archivos/{$valorResuelto}");
                        break;
                    case 'enlace':
                    case 'hora':
                        $displayValores[] = $valorResuelto;
                        break;
                    case 'fecha':
                        $displayValores[] = Carbon::parse($valorResuelto)->format('d/m/Y');
                        break;
                    default:
                        $displayValores[] = $valorResuelto;
                }
            }

            $resultado[] = [
                'etiqueta' => $campo->etiqueta,
                'tipo' => $tipoCampo,
                'valores' => $displayValores
            ];
        }

        return $resultado;
    }

    public function GetData($request, $formPrefix, $rules, $registro = null)
    {
        // Caso múltiple
        if ($registro) {

            $datosFormulario = collect($registro)
                ->filter(fn($value, $key) => str_starts_with($key, $formPrefix))
                ->toArray();

        } else {

            // Caso normal
            $inputs = $request->input($formPrefix, []);

            $datosFormulario = collect($inputs)
                ->mapWithKeys(function ($value, $key) use ($formPrefix) {
                    return ["{$formPrefix}[$key]" => $value];
                })
                ->toArray();
        }

        // Files
        $files = $request->file($formPrefix, []);

        foreach ($files as $key => $file) {
            $datosFormulario["{$formPrefix}[$key]"] = $file;
        }

        $data = [
            $formPrefix => $this->limpiarRegistro($datosFormulario)
        ];

        foreach ($files as $key => $file) {
            $data[$formPrefix][$key] = $file;
        }

        $validator = Validator::make($data, $rules);

        return compact('datosFormulario', 'validator');
    }

    public function obtenerFormularios($form, $moduloModelo)
    {
        $formulariosFinales = collect();
        $formularioModelo = Formulario::find($form);

        if ($moduloModelo) {

            $grupoData = $this->obtenerFormulariosDelGrupo($form, $moduloModelo->id);

            if ($grupoData && $grupoData['principal_id'] == $form) {

                foreach ($grupoData['formularios'] as $f) {
                    $formulariosFinales->push(Formulario::find($f['id']));
                }

            } else {
                $formulariosFinales->push($formularioModelo);
            }

        } else {
            $formulariosFinales->push($formularioModelo);
        }

        return $formulariosFinales;
    }

    public function obtenerFormulariosDelGrupo($formularioId, $moduloId)
    {
        $grupo = ModuloFormularioParalelo::where('modulo_id', $moduloId)->get()
            ->first(function ($g) use ($formularioId) {
                return collect($g->formularios)->pluck('id')->contains($formularioId);
            });

        if (!$grupo) {
            return null;
        }

        $formularios = collect($grupo->formularios);

        $formPrincipal = $formularios->firstWhere('es_principal', 1);

        return [
            'grupo' => $grupo,
            'formularios' => $formularios,
            'principal_id' => $formPrincipal['id'] ?? null
        ];
    }

    private function limpiarRegistro($registroData)
    {
        $registroLimpio = [];

        foreach ($registroData as $key => $value) {
            if (preg_match('/\[(.*?)\]/', $key, $matches)) {
                $registroLimpio[$matches[1]] = $value;
            }
        }

        return $registroLimpio;
    }

    public function GetFormRelacion($form, $relacion)
    {
        return Formulario::with($relacion)->findOrFail($form);
    }

    public function GetFormById($form)
    {
        return Formulario::find($form);
    }
    public function GetFormAll()
    {
        return Formulario::all();
    }

    public function EliminarArchivos($respuesta)
    {
        foreach ($respuesta->camposRespuestas as $campo) {
            $tipo = strtolower($campo->campo->campo_nombre ?? ''); // Asegúrate de tener la relación campo
            $valor = $campo->valor;

            if (in_array($tipo, ['imagen', 'video', 'archivo']) && $valor) {
                $path = match ($tipo) {
                    'imagen' => public_path("archivos/formulario_{$respuesta->form_id}/imagenes/{$valor}"),
                    'video' => public_path("archivos/formulario_{$respuesta->form_id}/videos/{$valor}"),
                    'archivo' => public_path("archivos/formulario_{$respuesta->form_id}/archivos/{$valor}"),
                    default => null,
                };

                if ($path && file_exists($path)) {
                    unlink($path);
                }
            }
        }

    }

}