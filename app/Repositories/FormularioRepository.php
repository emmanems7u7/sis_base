<?php

namespace App\Repositories;

use App\Interfaces\FormularioInterface;
use App\Interfaces\CatalogoInterface;
use App\Models\CamposForm;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use App\Models\RespuestasForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        ]);

        $this->ActualizarConfig($formulario, $request);


        return $formulario;
    }

    function ActualizarConfig($formulario, Request $request)
    {
        $config = $formulario->config ?? [];

        $config['crear_permisos'] = $request->has('crear_permisos') && $request->crear_permisos === 'on';
        $config['registro_multiple'] = $request->has('registro_multiple') && $request->registro_multiple === 'on';

        $formulario->update(['config' => $config]);
    }
    public function EditarFormulario($request, $formulario)
    {
        $formulario->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'slug' => Str::slug($request->nombre),
            'estado' => $request->estado,
        ]);
        $this->ActualizarConfig($formulario, $request);

    }


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

            // Solo validar si el request tiene datos para ese campo
            if (in_array($tipo, ['checkbox', 'radio', 'selector']) && $request->has($name)) {

                $valores = is_array($request->input($name))
                    ? $request->input($name)
                    : [$request->input($name)];

                //Caso 1: campo con categoria_id
                if ($campo->categoria_id) {
                    $opcionesValidas = $campo->opciones_catalogo->pluck('catalogo_codigo')->toArray();

                    foreach ($valores as $v) {
                        if (!in_array($v, $opcionesValidas)) {
                            $errores[] = "El valor '$v' no es válido para el campo '{$campo->etiqueta}'.";
                        }
                    }
                }

                //Caso 2: campo que referencia otro formulario
                elseif ($campo->form_ref_id) {
                    // Obtener los ids de respuestas del formulario referenciado
                    $respuestasValidas = $campo->formularioReferencia
                        ? $campo->formularioReferencia->respuestas->pluck('id')->toArray()
                        : [];

                    foreach ($valores as $v) {
                        if (!in_array($v, $respuestasValidas)) {
                            $errores[] = "El valor '$v' no es válido para el campo '{$campo->etiqueta}' (formulario referenciado).";
                        }
                    }
                }
            }
        }

        return $errores;
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

                        $respuestaReferencia = RespuestasForm::with('camposRespuestas')->find($respuesta->id);
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

        // 1️⃣ Si el campo tiene categoría (catalogo)
        if (!empty($campo->categoria_id)) {
            $catalogo = $this->CatalogoRepository->buscarPorDescripcion($campo->categoria_id, $valorUsuario);
            return $catalogo ? $catalogo->catalogo_codigo : null;
        }
        if (!empty($campo->form_ref_id)) {


            // Obtener el primer campo del formulario referenciado
            $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                ->orderBy('posicion', 'asc')
                ->first();

            if (!$campoReferencia) {
                return null;
            }


            // Obtener la respuesta (objeto) primero
            $respuestaCampo = RespuestasCampo::where('respuesta_id', $valorUsuario)
                ->where('cf_id', $campoReferencia->id)
                ->first();

            // Obtener valor si existe
            $valor = optional($respuestaCampo)->valor;

            return $valor; // devuelve el valor directo, no intenta acceder a $id
        }
        // 3️⃣ Si no tiene ni categoria_id ni form_ref_id, devolvemos el valor tal cual
        return $valorUsuario;
    }




    public function procesarFormularioConFiltros($formulario, Request $request, $pageName = null)
    {
        $camposPorNombre = $formulario->campos->keyBy('nombre');

        $query = RespuestasForm::where('form_id', $formulario->id)
            ->with('camposRespuestas.campo', 'actor');

        // FILTROS DINÁMICOS POR NOMBRE DE CAMPO
        $inputs = collect($request->all())
            ->except(['_token', 'page'])
            ->filter(fn($v) => $v !== null && $v !== '');

        foreach ($inputs as $nombreCampo => $valorEnviado) {
            if (!$camposPorNombre->has($nombreCampo))
                continue;

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

                    case 'selector':
                    case 'radio':
                        $q->where('valor', $valorEnviado);
                        break;

                    default:
                        $q->where('valor', $valorEnviado);
                        break;
                }
            });
        }

        // PAGINACIÓN
        $respuestas = $query->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], $pageName ?? 'page')
            ->withQueryString();

        // Cargar solo campos visibles en listado
        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->whereJsonContains('config->visible_listado', true)
                    ->orderBy('posicion');
            }
        ])->findOrFail($formulario->id);

        foreach ($respuestas as $respuesta) {
            foreach ($respuesta->camposRespuestas as $campoResp) {
                $campoResp->valor = $this->resolverValor($campoResp);
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
    public function resolverValor($campoRespOrCampo, $valor = null)
    {

        if ($valor === null) {
            $campoResp = $campoRespOrCampo;
            $campo = $campoResp->campo;
            $valor = $campoResp->valor;
        } else {

            $campo = $campoRespOrCampo;
        }


        if ($campo && $campo->form_ref_id) {

            $formulario = Formulario::find($campo->form_ref_id);

            $respuestaReferencia = RespuestasForm::with('camposRespuestas')->find($valor);
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
            $catalogo = $this->CatalogoRepository
                ->obtenerCatalogoPorCategoriaID($campo->categoria_id, $valor);
            return $catalogo?->catalogo_descripcion ?? $valor;
        }


        return $valor;
    }


    public function generar_informacion_export($respuestas, $formulario)
    {

        $datos = [];
        foreach ($respuestas as $respuesta) {
            $fila = [];

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

}