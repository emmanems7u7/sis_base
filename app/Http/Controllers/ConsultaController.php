<?php

namespace App\Http\Controllers;

use App\Interfaces\FormularioInterface;
use App\Models\CamposForm;
use App\Models\Consulta;
use App\Models\Formulario;
use App\Models\RespuestasForm;
use Illuminate\Http\Request;

class ConsultaController extends Controller
{

    protected $formularioRepository;
    public function __construct(FormularioInterface $formularioRepository)
    {
        $this->formularioRepository = $formularioRepository;
    }

    public function index()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Motor de consultas', 'url' => ''],
        ];
        $consultas = Consulta::latest()->get();

        return view(
            'consultas.index',
            compact('consultas', 'breadcrumb')
        );
    }

    public function create()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Motor de consultas', 'url' => route('consultas.index')],
            ['name' => 'Crear Consulta', 'url' => ''],
        ];
        $formularios = Formulario::all();

        return view(
            'consultas.create',
            compact('formularios', 'breadcrumb')
        );
    }

    public function store(Request $request)
    {
        Consulta::create([
            'nombre' => $request->nombre,
            'formulario_id' => $request->formulario_id,
            'configuracion' => json_decode(
                $request->configuracion,
                true
            )
        ]);

        return redirect()
            ->route('consultas.index');
    }
    public function edit(Consulta $consulta)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Motor de consultas', 'url' => route('consultas.index')],
            ['name' => 'Editar Consulta', 'url' => ''],
        ];

        $formularios = Formulario::all();

        return view(
            'consultas.edit',
            compact(
                'consulta',
                'formularios',
                'breadcrumb'
            )
        );
    }
    public function update(Request $request, Consulta $consulta)
    {

        $consulta->update([
            'nombre' => $request->nombre,
            'formulario_id' => $request->formulario_id,
            'configuracion' => json_decode(
                $request->configuracion,
                true
            )
        ]);

        return redirect()
            ->route('consultas.index');
    }

    public function ejecutar(Consulta $consulta)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Motor de consultas', 'url' => route('consultas.index')],
            ['name' => 'Consulta', 'url' => ''],
        ];

        $resultado = $this->execute($consulta);
        $config = $consulta->configuracion;

        $columnas = $this->obtenerEtiquetasColumnas($config['select'] ?? []);


        return view(
            'consultas.resultado',
            compact(
                'columnas',
                'consulta',
                'resultado',
                'breadcrumb'
            )
        );
    }
    protected function obtenerEtiquetasColumnas(array $ids)
    {
        return collect($ids)->mapWithKeys(function ($id) {

            $campoId = last(explode('.', $id));

            $campo = CamposForm::find($campoId);

            return [
                $id => $campo?->etiqueta ?? $id
            ];
        });
    }
    public function execute($consulta)
    {
        $formulario = $consulta->formulario;
        $config = $consulta->configuracion;

        $query = $this->buildQuery($formulario, $config);

        $camposNecesarios = $this->obtenerCamposNecesarios($config);



        $respuestas = $query->with([
            'camposRespuestas' => function ($q) use ($camposNecesarios) {
                $q->whereIn('cf_id', $camposNecesarios);
            },
            'camposRespuestas.campo'
        ])->get();



        $rows = $this->transform($respuestas);



        $rows = $this->applySelect(
            $rows,
            $config['select'] ?? []
        );


        return $rows->values();
    }

    protected function obtenerCamposNecesarios(array $config)
    {
        $campos = [];

        foreach ($config['select'] ?? [] as $campo) {

            $partes = explode('.', $campo);

            $campos[] = $partes[0];
        }

        foreach ($config['where'] ?? [] as $where) {

            $partes = explode('.', $where['campo']);

            $campos[] = $partes[0];
        }

        foreach ($config['orderBy'] ?? [] as $order) {

            $partes = explode('.', $order['campo']);

            $campos[] = $partes[0];
        }

        return collect($campos)
            ->unique()
            ->values()
            ->all();
    }

    protected function buildQuery($formulario, array $config)
    {
        $query = $formulario->respuestas();

        // WHERE
        foreach ($config['where'] ?? [] as $filter) {

            $campoId = $filter['campo'];
            $operador = $filter['operador'];
            $valor = $filter['valor'];

            $query->whereHas('camposRespuestas', function ($q) use ($campoId, $operador, $valor) {

                $q->where('cf_id', $campoId);

                match ($operador) {
                    '=' => $q->where('valor', '=', $valor),
                    '!=' => $q->where('valor', '!=', $valor),
                    '>' => $q->where('valor', '>', $valor),
                    '>=' => $q->where('valor', '>=', $valor),
                    '<' => $q->where('valor', '<', $valor),
                    '<=' => $q->where('valor', '<=', $valor),
                    'like' => $q->where('valor', 'like', "%{$valor}%"),
                    'not like' => $q->where('valor', 'not like', "%{$valor}%"),
                    'starts_with' => $q->where('valor', 'like', "{$valor}%"),
                    'ends_with' => $q->where('valor', 'like', "%{$valor}"),
                    'is_null' => $q->whereNull('valor'),
                    'is_not_null' => $q->whereNotNull('valor'),
                    'empty' => $q->where(function ($sub) {
                            $sub->whereNull('valor')
                            ->orWhere('valor', '');
                        }),
                    'not_empty' => $q->where(function ($sub) {
                            $sub->whereNotNull('valor')
                            ->where('valor', '!=', '');
                        }),
                    default => null
                };
            });
        }

        // ORDER BY
        $joins = [];

        foreach ($config['orderBy'] ?? [] as $order) {

            $campoId = $order['campo'];
            $direccion = strtolower($order['direccion']);

            $alias = 'ord_' . $campoId;

            if (!isset($joins[$alias])) {

                $query->leftJoin(
                    'respuestas_campos as ' . $alias,
                    function ($join) use ($alias, $campoId) {

                        $join->on($alias . '.respuesta_id', '=', 'respuestas_forms.id')
                            ->where($alias . '.cf_id', $campoId);
                    }
                );

                $joins[$alias] = true;
            }

            $query->orderBy(
                $alias . '.valor',
                $direccion
            );
        }

        $query->select('respuestas_forms.*');

        return $query;
    }


    protected function transform($respuestas)
    {


        return collect($respuestas)->map(function ($respuesta) {

            $row = [
                '_id' => $respuesta->id
            ];

            foreach ($respuesta->camposRespuestas as $campo) {

                $row[$campo->cf_id] = $campo->valor;
            }

            return $row;
        });
    }

    protected function applySelect($rows, $select)
    {
        // dump($select);
        if (empty($select)) {
            return $rows;
        }

        return $rows->map(function ($row) use ($select) {

            $resultado = [];

            foreach ($select as $campo) {

                $resultado[$campo] =
                    $this->resolverCampo($row, $campo);
            }

            return $resultado;
        });
    }
    public function campos(Formulario $formulario)
    {

        $formulario->load('campos');

        return response()->json($this->obtenerCamposRelacionados($formulario));
    }

    protected function obtenerCamposRelacionados(
        Formulario $formulario,
        string $prefijoNombre = '',
        string $etiquetaPadre = '',
        string $prefijoId = '',
        int $nivel = 0,
        ?string $padreId = null
    ) {

        $resultado = [];

        foreach ($formulario->campos as $campo) {

            $nombreCompleto = $prefijoNombre
                ? "{$prefijoNombre}.{$campo->nombre}"
                : $campo->nombre;

            $etiquetaCompleta = $etiquetaPadre
                ? "{$etiquetaPadre} → {$campo->etiqueta}"
                : $campo->etiqueta;

            $idCompuesto = $prefijoId
                ? "{$prefijoId}.{$campo->id}"
                : (string) $campo->id;

            $esRelacion = !empty($campo->form_ref_id);
            $resultado[] = [

                'id' => $idCompuesto,

                'campo_id' => $campo->id,

                'nombre' => $nombreCompleto,

                'etiqueta' => $etiquetaCompleta,

                'etiqueta_simple' => $campo->etiqueta,

                'tipo' => $campo->tipo,

                'form_ref_id' => $campo->form_ref_id,

                'nivel' => $nivel,

                'padre_id' => $padreId,

                'es_relacion' => $esRelacion

            ];

            if ($esRelacion) {

                $formRelacionado = Formulario::find($campo->form_ref_id);

                if ($formRelacionado) {

                    $resultado = array_merge(
                        $resultado,
                        $this->obtenerCamposRelacionados(
                            $formRelacionado,
                            $nombreCompleto,
                            $etiquetaCompleta,
                            $idCompuesto,
                            $nivel + 1,
                            $idCompuesto
                        )
                    );
                }
            }
        }

        return $resultado;
    }
    protected function resolvePath(array $row, string $path)
    {

        $partes = explode('.', $path);

        $valor = $row;

        foreach ($partes as $parte) {

            if (!is_array($valor)) {
                return null;
            }

            $valor = $valor[$parte] ?? null;
        }

        return $valor;
    }
    protected function getRelatedValue($respuestaId, $campoDestino)
    {

        $respuesta = RespuestasForm::with('camposRespuestas.campo')->find($respuestaId);

        if (!$respuesta) {
            return null;
        }

        foreach ($respuesta->camposRespuestas as $campo) {

            if ($campo->campo->id === $campoDestino) {

                return $campo->valor;
            }
        }


        return null;
    }

    protected function resolverCampo($row, $campo)
    {
        // Campo normal
        if (!str_contains($campo, '.')) {

            $campoModel = CamposForm::find($campo);


            if (!$campoModel) {
                return $row[$campo] ?? null;
            }

            return $this->formularioRepository->obtenerValorReal(
                $campoModel,
                $row[$campo] ?? null
            );
        }

        // Campo relacionado
        $partes = explode('.', $campo);

        $campoRaiz = array_shift($partes);

        $valorRelacion = $row[$campoRaiz] ?? null;

        if (!$valorRelacion) {
            return null;
        }

        $campoRaizDef = CamposForm::find($campoRaiz);

        if (!$campoRaizDef) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Caso especial:
        | El campo tiene form_ref_id pero guarda un valor visible
        | (ej: VTA-004) en lugar del respuesta_id.
        |--------------------------------------------------------------------------
        */
        if (
            $campoRaizDef->form_ref_id &&
            !is_numeric($valorRelacion)
        ) {

            $campoClave = CamposForm::where(
                'form_id',
                $campoRaizDef->form_ref_id
            )
                ->orderBy('posicion')
                ->first();

            if ($campoClave) {

                $respuestaRelacionada = RespuestasForm::where(
                    'form_id',
                    $campoRaizDef->form_ref_id
                )
                    ->whereHas(
                        'camposRespuestas',
                        function ($q) use ($campoClave, $valorRelacion) {

                            $q->where(
                                'cf_id',
                                $campoClave->id
                            )
                                ->where(
                                    'valor',
                                    $valorRelacion
                                );
                        }
                    )
                    ->first();

                if ($respuestaRelacionada) {
                    $valorRelacion = $respuestaRelacionada->id;
                }
            }
        }

        return $this->resolverRelacion(
            $valorRelacion,
            $partes
        );
    }

    protected function resolverRelacion($respuestaId, array $partes)
    {
        $respuesta = RespuestasForm::with('camposRespuestas')
            ->find($respuestaId);

        if (!$respuesta) {
            return null;
        }

        $campoId = array_shift($partes);

        $campoDef = CamposForm::find($campoId);

        if (!$campoDef) {
            return null;
        }

        $respuestaCampo = $respuesta->camposRespuestas
            ->firstWhere('cf_id', $campoId);

        if (!$respuestaCampo) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Si ya llegamos al último campo solicitado
        |--------------------------------------------------------------------------
        */
        if (empty($partes)) {

            return $this->formularioRepository->obtenerValorReal(
                $campoDef,
                $respuestaCampo->valor
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Si todavía quedan partes por resolver,
        | el campo actual DEBE ser una relación.
        |--------------------------------------------------------------------------
        */
        if (!$campoDef->form_ref_id) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Caso A:
        | El valor guarda directamente el ID de respuesta relacionada
        |--------------------------------------------------------------------------
        */
        $respuestaRelacionada = RespuestasForm::find(
            $respuestaCampo->valor
        );

        /*
        |--------------------------------------------------------------------------
        | Caso B:
        | Si no existe, buscar por el campo clave del formulario relacionado
        |--------------------------------------------------------------------------
        */
        if (!$respuestaRelacionada) {

            $campoClave = CamposForm::where(
                'form_id',
                $campoDef->form_ref_id
            )
                ->orderBy('posicion')
                ->first();

            if ($campoClave) {

                $respuestaRelacionada = RespuestasForm::where(
                    'form_id',
                    $campoDef->form_ref_id
                )
                    ->whereHas('camposRespuestas', function ($q) use ($campoClave, $respuestaCampo) {
                        $q->where('cf_id', $campoClave->id)
                            ->where('valor', $respuestaCampo->valor);
                    })
                    ->first();
            }
        }

        if (!$respuestaRelacionada) {
            return null;
        }

        return $this->resolverRelacion(
            $respuestaRelacionada->id,
            $partes
        );
    }
}
