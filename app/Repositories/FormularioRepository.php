<?php

namespace App\Repositories;

use App\Interfaces\FormularioInterface;
use App\Interfaces\CatalogoInterface;
use App\Models\CamposForm;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use App\Models\RespuestasForm;
use Illuminate\Http\Request;

class FormularioRepository implements FormularioInterface
{
    protected $model;
    protected $CatalogoRepository;

    public function __construct(Formulario $model, CatalogoInterface $catalogoInterface)
    {
        $this->model = $model;
        $this->CatalogoRepository = $catalogoInterface;
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


            if ($campo->categoria_id) {
                $campo->opciones_catalogo = $this->CatalogoRepository
                    ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true, $limit, $offset);


            } elseif ($campo->form_ref_id) {
                $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                    ->orderBy('posicion', 'asc')
                    ->first();

                if ($campoReferencia) {
                    $campo->opciones_catalogo = $campo->opcionesFormularioQuery() // esto devuelve query builder
                        ->offset($offset)
                        ->limit($limit)
                        ->get()
                        ->map(function ($respuesta) use ($campoReferencia) {
                            $valorCampo = $respuesta->camposRespuestas
                                ->firstWhere('cf_id', $campoReferencia->id);

                            return (object) [
                                'catalogo_codigo' => $respuesta->id,
                                'catalogo_descripcion' => $valorCampo->valor ?? 'Sin nombre',
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
        // Mapear campos por nombre
        $camposPorNombre = $formulario->campos->keyBy('nombre');

        // Query base de respuestas
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

        // PAGINACIÓN (nombre de página independiente si se pasa)
        $respuestas = $query->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], $pageName ?? 'page')
            ->withQueryString();

        // PROCESAR CAMPOS
        $formulario = Formulario::with(['campos' => fn($q) => $q->orderBy('posicion')])
            ->findOrFail($formulario->id);

        $camposProcesados = $this->CamposFormCat($formulario->campos);
        $formulario->campos = $camposProcesados;

        return [
            'formulario' => $formulario,
            'respuestas' => $respuestas,
        ];
    }
}