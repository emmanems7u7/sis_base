<?php

namespace App\Http\Controllers;

use App\Interfaces\FormularioInterface;
use App\Models\CamposForm;
use App\Models\Consulta;
use App\Models\Formulario;
use App\Models\RespuestasForm;
use App\Reportes\SelectBuilder;
use Illuminate\Http\Request;
use PSpell\Config;
use App\Interfaces\CamposFormInterface;
use App\Reportes\FilterBuilder;
use App\Reportes\RutaCompiler;
use App\Reportes\JoinBuilder;
use App\Reportes\QueryContext;
use App\Reportes\QueryPlan;

class ConsultaController extends Controller
{

    protected $formularioRepository;
    protected $CamposFormRepository;

    public function __construct(FormularioInterface $formularioRepository, CamposFormInterface $CamposFormRepository)
    {
        $this->formularioRepository = $formularioRepository;
        $this->CamposFormRepository = $CamposFormRepository;

    }

    public function index(Formulario $formulario)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Motor de consultas', 'url' => ''],
        ];

        $consultas = Consulta::where('formulario_id', $formulario->id)
            ->latest()
            ->get();

        return view('consultas.index', compact('consultas', 'breadcrumb', 'formulario'));
    }

    public function create(Formulario $formulario)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Motor de consultas', 'url' => route('consultas.index', $formulario)],
            ['name' => 'Crear Consulta', 'url' => ''],
        ];



        $campos = $this->obtenerCamposRelacionados($formulario);


        return view('consultas.create', compact('formulario', 'breadcrumb', 'campos'));
    }

    public function store(Request $request, Formulario $formulario)
    {

        Consulta::create([
            'nombre' => $request->nombre,
            'formulario_id' => $formulario->id,
            'configuracion' => json_decode(
                $request->configuracion,
                true
            )
        ]);

        return redirect()->route('consultas.index', $formulario);
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

    public function destroy(Consulta $consulta)
    {

        $consulta->delete();

        return redirect()->back()->with('status', 'Consulta eliminada correctamente.');
    }
    public function show(Consulta $consulta)
    {






        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Reporte Formulario', 'url' => route('consultas.index', $consulta->formulario_id)],
            ['name' => 'Consulta', 'url' => ''],
        ];


        $formulario = Formulario::find($consulta->formulario_id);


        $filtros = $consulta->configuracion;


        if (is_string($filtros)) {
            $filtros = json_decode($filtros, true);
        }



        $campos = $this->obtenerCamposRelacionados($formulario);

        $mapaCampos = collect($campos)->keyBy('id');


        $where = [];

        foreach ($filtros['where'] ?? [] as $filtro) {


            $campoInfo = $mapaCampos->get($filtro['campo']);


            if ($campoInfo) {

                $filtro['etiqueta'] = $campoInfo['etiqueta'];
                $filtro['tipo_campo'] = $campoInfo['tipo'];

                $campoModel = CamposForm::find($campoInfo['campo_id']);

                $filtro['opciones'] = [];


                if ($campoModel) {


                    if ($campoModel->categoria_id || $campoModel->form_ref_id) {

                        $campoProcesado = $this->CamposFormRepository->CamposFormCat(collect([$campoModel]))->first();

                        if ($campoProcesado) {

                            $filtro['opciones'] = $campoProcesado->opciones_catalogo->values()->all();

                        }

                    }

                }

            }


            $where[] = $filtro;

        }


        $filtros['where'] = $where;


        unset($filtro);




        return view('consultas.show', compact(
            'consulta',
            'breadcrumb',
            'formulario',
            'filtros',
            'campos'
        ));

    }
    public function ejecutar(
        Request $request,
        Consulta $consulta
    ) {
        $config = $consulta->configuracion;


        if (is_string($config)) {

            $config = json_decode(
                $config,
                true
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Agregar valores ingresados al where
        |--------------------------------------------------------------------------
        */

        $filtrosRequest = $request->input('filtros', []);


        foreach (($config['where'] ?? []) as $i => $where) {

            $campo = $where['campo'];

            if (array_key_exists($campo, $filtrosRequest)) {
                $config['where'][$i]['valor'] = $filtrosRequest[$campo];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Ejecutar consulta
        |--------------------------------------------------------------------------
        */

        $resultado = $this->execute(
            $consulta,
            $config
        );


        /*
        |--------------------------------------------------------------------------
        | Obtener columnas
        |--------------------------------------------------------------------------
        */

        $columnas =
            $this->obtenerEtiquetasColumnas(
                $config['select'] ?? []
            );


        return response()->json([

            'columnas' => $columnas,

            'datos' => $resultado

        ]);

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
    public function execute(
        $consulta,
        array $config
    ) {

        $formulario = $consulta->formulario;

        /*
        |--------------------------------------------------------------------------
        | Construir consulta SQL (solo filtros del formulario principal)
        |--------------------------------------------------------------------------
        */

        $query = $this->buildQuery(
            $formulario,
            $config
        );
        dd($query);
        /*
        |--------------------------------------------------------------------------
        | Obtener respuestas
        |--------------------------------------------------------------------------
        */

        $camposNecesarios = $this->obtenerCamposNecesarios(
            $config
        );

        $respuestas = $query
            ->with([
                'camposRespuestas' => function ($q) use ($camposNecesarios) {

                    $q->whereIn(
                        'cf_id',
                        $camposNecesarios
                    );

                },

                'camposRespuestas.campo'
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Aplicar filtros relacionados
        |--------------------------------------------------------------------------
        */

        $respuestas = $respuestas->filter(function ($respuesta) use ($config) {

            foreach ($config['where'] ?? [] as $filter) {

                if (
                    !isset($filter['valor']) ||
                    $filter['valor'] === '' ||
                    $filter['valor'] === null
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Si es un campo del formulario principal
                |--------------------------------------------------------------------------
                */

                if (!str_contains($filter['campo'], '.')) {
                    continue;
                }

                if (
                    !$this->cumpleFiltroRelacionado(
                        $respuesta,
                        $filter
                    )
                ) {
                    return false;
                }
            }

            return true;

        })->values();

        /*
        |--------------------------------------------------------------------------
        | Transformar filas
        |--------------------------------------------------------------------------
        */

        $rows = $this->transform(
            $respuestas
        );

        /*
        |--------------------------------------------------------------------------
        | Select
        |--------------------------------------------------------------------------
        */

        return $this->applySelect(
            $rows,
            $config['select'] ?? []
        );

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
    protected function buildQuery(
        $formulario,
        array $config
    ) {

        $query = $formulario
            ->respuestas()
            ->getQuery();


        $context = new QueryContext();


        /*
        |--------------------------------------------------------------------------
        | Crear plan de joins
        |--------------------------------------------------------------------------
        */

        $plan = new QueryPlan();


        $compiler = new RutaCompiler();



        /*
        |--------------------------------------------------------------------------
        | Relaciones de filtros
        |--------------------------------------------------------------------------
        */

        foreach ($config['where'] ?? [] as $filter) {


            if (
                !isset($filter['valor']) ||
                $filter['valor'] === '' ||
                $filter['valor'] === null
            ) {
                continue;
            }


            $ruta = $compiler->compile(
                $formulario,
                $filter['campo']
            );


            $plan->agregar(
                $ruta,
                $filter
            );

        }



        /*
        |--------------------------------------------------------------------------
        | Relaciones de campos seleccionados
        |--------------------------------------------------------------------------
        */

        foreach ($config['select'] ?? [] as $campo) {


            $ruta = $compiler->compile(
                $formulario,
                $campo
            );


            $plan->agregar(
                $ruta,
                []
            );

        }



        /*
        |--------------------------------------------------------------------------
        | Construir joins
        |--------------------------------------------------------------------------
        */


        $joinBuilder = new JoinBuilder(
            $query,
            $context
        );


        $query = $joinBuilder->apply(
            $plan
        );




        /*
        |--------------------------------------------------------------------------
        | Seleccionar columnas
        |--------------------------------------------------------------------------
        */


        $selectBuilder = new SelectBuilder(
            $query,
            $context,
            $formulario
        );


        $query = $selectBuilder->aplicar(
            $config['select']
        );



        /*
        |--------------------------------------------------------------------------
        | Ejecutar consulta
        |--------------------------------------------------------------------------
        */


        return $query->get();

    }

    protected function cumpleFiltroRelacionado(
        RespuestasForm $respuesta,
        array $filter
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Construir el mismo row que usa applySelect()
        |--------------------------------------------------------------------------
        */

        $row = [
            '_id' => $respuesta->id
        ];

        foreach ($respuesta->camposRespuestas as $campo) {

            $row[$campo->cf_id] = $campo->valor;

        }

        /*
        |--------------------------------------------------------------------------
        | Resolver cualquier ruta:
        | 15
        | 15.11
        | 16.3.26
        | 16.3.4.7
        |--------------------------------------------------------------------------
        */

        $valorActual = $this->resolverCampo(
            $row,
            $filter['campo']
        );

        return $this->compararValor(
            $valorActual,
            $filter
        );

    }

    protected function compararValor(
        $valorActual,
        array $filter
    ): bool {

        $tipoCampo = $filter['tipo_campo'];

        $tipoFiltro = $filter['tipo'];

        $valor = $filter['valor'];

        /*
        |--------------------------------------------------------------------------
        | Si no existe valor
        |--------------------------------------------------------------------------
        */

        if ($valorActual === null) {

            return in_array(
                $tipoFiltro,
                [
                    'vacio'
                ]
            );

        }

        /*
        |--------------------------------------------------------------------------
        | TEXTO
        |--------------------------------------------------------------------------
        */

        if ($tipoCampo == 'CAMPF-012') {

            return match ($tipoFiltro) {

                'contiene'
                => str_contains(
                    mb_strtolower($valorActual),
                    mb_strtolower($valor)
                ),

                'igual'
                => $valorActual == $valor,

                'empieza'
                => str_starts_with(
                    mb_strtolower($valorActual),
                    mb_strtolower($valor)
                ),

                'termina'
                => str_ends_with(
                    mb_strtolower($valorActual),
                    mb_strtolower($valor)
                ),

                'vacio'
                => $valorActual === null || $valorActual === '',

                'novacio'
                => $valorActual !== null && $valorActual !== '',

                default => true

            };

        }

        /*
        |--------------------------------------------------------------------------
        | NUMERO
        |--------------------------------------------------------------------------
        */

        if ($tipoCampo == 'CAMPF-013') {

            return match ($tipoFiltro) {

                'igual'
                => $valorActual == $valor,

                'mayor'
                => $valorActual > $valor,

                'menor'
                => $valorActual < $valor,

                'rango'
                =>

                (
                    (!isset($valor['desde']) || $valorActual >= $valor['desde'])
                    &&
                    (!isset($valor['hasta']) || $valorActual <= $valor['hasta'])
                ),

                default => true

            };

        }

        /*
        |--------------------------------------------------------------------------
        | SELECT / RADIO / CHECKBOX
        |--------------------------------------------------------------------------
        */

        if (
            in_array($tipoCampo, [

                'CAMPF-015',

                'CAMPF-016',

                'CAMPF-017'

            ])
        ) {

            return match ($tipoFiltro) {

                'igual'
                => $valorActual == $valor,

                'contiene'
                => str_contains(
                    mb_strtolower($valorActual),
                    mb_strtolower($valor)
                ),

                default => true

            };

        }

        /*
        |--------------------------------------------------------------------------
        | FECHA
        |--------------------------------------------------------------------------
        */

        if ($tipoCampo == 'CAMPF-021') {

            if ($tipoFiltro == 'rango') {

                if (
                    !empty($valor['desde']) &&
                    $valorActual < $valor['desde']
                ) {

                    return false;

                }

                if (
                    !empty($valor['hasta']) &&
                    $valorActual > $valor['hasta']
                ) {

                    return false;

                }

                return true;

            }

        }

        /*
        |--------------------------------------------------------------------------
        | HORA
        |--------------------------------------------------------------------------
        */

        if ($tipoCampo == 'CAMPF-022') {

            return match ($tipoFiltro) {

                'igual'
                => $valorActual == $valor,

                'desde'
                => $valorActual >= $valor,

                'hasta'
                => $valorActual <= $valor,

                default => true

            };

        }

        return true;

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
