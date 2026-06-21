<?php

namespace App\Http\Controllers;

use App\Interfaces\CamposFormInterface;
use App\Models\CamposForm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ConfiguracionCredenciales;
use App\Models\ContenedorGrid;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use App\Interfaces\FormularioInterface;

use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{


    protected $FormularioRepository;
    protected $CamposFormRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FormularioInterface $formularioInterface, CamposFormInterface $camposFormInterface)
    {
        $this->FormularioRepository = $formularioInterface;
        $this->CamposFormRepository = $camposFormInterface;


        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $rolNombre = $user->roles->first()?->name;

        Cache::forget('contenedor_grid_' . $rolNombre);
        $contenedor = Cache::remember(
            'contenedor_grid_' . $rolNombre,
            now()->addHours(12),
            function () use ($rolNombre) {

                return ContenedorGrid::with('filas.columnas.widget')->whereHas('role', fn($q) => $q->where('name', $rolNombre))->first();
            }
        );

        $grid = [];

        if ($contenedor) {
            foreach ($contenedor->filas as $fila) {

                $filaData = ['columnas' => []];

                foreach ($fila->columnas as $col) {

                    if (!$col->widget)
                        continue;

                    $widget = $this->resolverWidget($col->widget);

                    $filaData['columnas'][] = [
                        'clases' => $col->clases_bootstrap,
                        'widget' => $widget
                    ];
                }

                $grid[] = $filaData;
            }
        }
        return view('home', [
            'grid' => $grid,
            'breadcrumb' => [
                ['name' => 'Inicio', 'url' => route('home')],
            ],
            'contenedor' => $contenedor,
            'tiempo_cambio_contraseña' => $this->validarCambioPassword()
        ]);
    }
    private function validarCambioPassword(): int
    {
        $config = ConfiguracionCredenciales::first();

        if (!$config) {
            return 1;
        }

        $user = Auth::user();

        if (!$user->usuario_fecha_ultimo_password) {
            return 1;
        }

        $ultimoCambio = Carbon::parse($user->usuario_fecha_ultimo_password);
        $dias = $ultimoCambio->diffInDays(now());

        return ($dias >= $config->conf_duracion_max) ? 1 : 2;
    }

    private function resolverWidget($widget): array
    {
        switch ($widget->tipo) {

            case 'WID-010': // Contador
                return $this->resolverContador($widget);

            case 'WID-006': // Formulario
                return [
                    'tipo' => 'WID-006',
                    'data' => [
                        'formulario' => Formulario::with('campos')
                            ->find($widget->formulario_id),
                        'modulo' => $widget->modulo_id
                    ],
                ];

            case 'WID-002': // Estadística
                return $this->resolverEstadistica($widget);

            case 'WID-001': // Botón
                return [
                    'tipo' => 'WID-001',
                    'data' => [
                        'configuracion' => $widget->configuracion ?? [],
                    ],
                ];

            case 'WID-004':
                $request = new Request();
                $resultado = $this->FormularioRepository->procesarFormularioConFiltros(Formulario::with('campos')->findOrFail($widget->formulario_id), $request);

                $campos = $resultado['formulario']->campos;

                return [
                    'tipo' => 'WID-004',
                    'data' => [
                        'campos' => $campos,
                        'resultado' => $resultado,

                    ],
                ];

            case 'WID-007': // Barra
            case 'WID-008': // Línea
            case 'WID-009': // Pastel
                return $this->resolverGrafico($widget, true);


            default:
                return [
                    'tipo' => 'DEFAULT',
                    'data' => [
                        'nombre' => $widget->nombre,
                    ],
                ];
        }
    }

    private function resolverGrafico($widget, bool $home = false, ?string $fechaDesde = null, ?string $fechaHasta = null): array
    {

        $config = $widget->configuracion;

        $campoX = $config['campo_x_id'] ?? null;
        $campoY = $config['campo_y_id'] ?? null;

        $tipo = $config['tipo'] ?? 'conteo';
        $periodo = $config['periodo'] ?? null;
        $orden = $config['orden'] ?? 'asc';
        $limite = $config['limite'] ?? null;

        if (!$campoX) {
            return [
                'id' => $widget->id,
                'tipo' => $widget->tipo,
                'configuracion' => $config,
                'data' => []
            ];
        }

        $query = $widget->formulario
            ->respuestas()
            ->with('camposRespuestas');

        if ($home) {

            $hoy = now();

            if ($periodo === 'dia') {

                $query->whereYear('created_at', $hoy->year)
                    ->whereMonth('created_at', $hoy->month);

            } elseif ($periodo === 'mes') {

                $query->whereYear('created_at', $hoy->year);
            }

        } else {

            if ($fechaDesde) {
                $query->whereDate('created_at', '>=', $fechaDesde);
            }

            if ($fechaHasta) {
                $query->whereDate('created_at', '<=', $fechaHasta);
            }
        }

        $respuestas = $query->get();

        $datos = [];
        $conteos = [];

        foreach ($respuestas as $respuesta) {

            $respuestaCampoX = $respuesta->camposRespuestas
                ->firstWhere('cf_id', $campoX);

            if (!$respuestaCampoX) {
                continue;
            }

            if ($periodo) {

                $fecha = $respuesta->created_at;

                $clave = match ($periodo) {
                    'dia' => $fecha->format('Y-m-d'),
                    'mes' => $fecha->format('Y-m'),
                    'anio' => $fecha->format('Y'),
                    default => $respuestaCampoX->valor,
                };

            } else {

                $clave = $respuestaCampoX->valor;
            }

            if ($campoY) {

                $respuestaCampoY = $respuesta->camposRespuestas
                    ->firstWhere('cf_id', $campoY);

                $valor = $respuestaCampoY
                    ? (float) $respuestaCampoY->valor
                    : 0;

            } else {

                $valor = 1;
            }

            if (!isset($datos[$clave])) {
                $datos[$clave] = [];
            }

            $datos[$clave][] = $valor;

            $conteos[$clave] = ($conteos[$clave] ?? 0) + 1;
        }

        $resultados = [];

        foreach ($datos as $clave => $valores) {

            $resultados[$clave] = match ($tipo) {

                'conteo' => $conteos[$clave],

                'suma' => array_sum($valores),

                'promedio' => count($valores)
                ? round(array_sum($valores) / count($valores), 2)
                : 0,

                'maximo' => max($valores),

                'minimo' => min($valores),

                default => array_sum($valores),
            };
        }

        if ($orden === 'asc') {
            ksort($resultados);
        } else {
            krsort($resultados);
        }

        if ($limite) {

            $resultados = array_slice(
                $resultados,
                0,
                (int) $limite,
                true
            );
        }

        $labels = array_keys($resultados);

        if (!$periodo) {

            $campo = $this->CamposFormRepository->GetCampo($campoX);

            foreach ($labels as &$label) {

                $label = $this->FormularioRepository
                    ->obtenerValorReal($campo, $label);
            }

            unset($label);
        }

        return [
            'id' => $widget->id,
            'tipo' => $widget->tipo,

            'configuracion' => [
                'titulo' => $config['titulo'] ?? 'Datos',
                'subtitulo' => $config['subtitulo'] ?? null,
                'color' => $config['color'] ?? '#0d6efd',
                'color_fondo' => $config['color_fondo'] ?? null,
                'color_texto' => $config['color_texto'] ?? null,
                'mostrar_leyenda' => $config['mostrar_leyenda'] ?? false,
                'mostrar_etiquetas' => $config['mostrar_etiquetas'] ?? false,
                'animacion' => $config['animacion'] ?? false,
                'altura' => $config['altura'] ?? null,
                'ancho' => $config['ancho'] ?? null,
            ],

            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => $config['titulo'] ?? 'Datos',
                        'backgroundColor' => $config['color'] ?? '#0d6efd',
                        'borderColor' => $config['color'] ?? '#0d6efd',
                        'data' => array_values($resultados),
                    ]
                ]
            ]
        ];
    }
    private function resolverEstadistica($widget): array
    {
        $config = $widget->configuracion ?? [];

        $campoId = $config['campo_id'] ?? null;
        $tipo = $config['tipo_estadistica'] ?? 'total';
        $filtros = $config['filtros'] ?? [];

        $resultado = 0;

        if ($campoId) {

            $query = RespuestasCampo::query()
                ->where('cf_id', $campoId);

            if (($filtros['fecha'] ?? null) === 'anio_actual') {
                $query->whereYear('created_at', now()->year);
            }

            if (
                !empty($filtros['campo']['cf_id']) &&
                !empty($filtros['campo']['valor'])
            ) {
                $query->whereHas('respuesta.camposRespuestas', function ($q) use ($filtros) {
                    $q->where('cf_id', $filtros['campo']['cf_id'])
                        ->where('valor', $filtros['campo']['valor']);
                });
            }

            switch ($tipo) {
                case 'conteo':
                    $resultado = $query->count();
                    break;

                case 'suma':
                    $resultado = $query->sum('valor');
                    break;

                case 'promedio':
                    $resultado = round($query->avg('valor'), 2);
                    break;

                default: // total
                    $resultado = $query->count();
                    break;
            }
        }

        $campo = CamposForm::find($campoId);

        return [
            'tipo' => 'WID-002',
            'data' => [
                'resultado' => $resultado,
                'tipo_estadistica' => $tipo,
                'campo' => $campo?->etiqueta ?? 'Campo no definido',
                'fecha' => $filtros['fecha'] ?? null,
            ],
        ];
    }


    private function resolverContador($widget): array
    {
        $config = $widget->configuracion ?? [];

        $query = $widget->formulario->respuestas();

        /*
        |--------------------------------------------------------------------------
        | Periodo
        |--------------------------------------------------------------------------
        */

        $periodo = $config['periodo'] ?? null;

        if ($periodo) {

            $hoy = now();

            match ($periodo) {

                'hoy' => $query->whereDate('created_at', $hoy),

                'semana' => $query->whereBetween(
                    'created_at',
                    [
                        $hoy->copy()->startOfWeek(),
                        $hoy->copy()->endOfWeek()
                    ]
                ),

                'mes' => $query
                    ->whereYear('created_at', $hoy->year)
                    ->whereMonth('created_at', $hoy->month),

                'anio' => $query
                    ->whereYear('created_at', $hoy->year),

                default => null
            };
        }

        /*
        |--------------------------------------------------------------------------
        | Filtros
        |--------------------------------------------------------------------------
        */

        $filtros = $config['filtros'] ?? [];

        foreach ($filtros as $filtro) {

            if (
                empty($filtro['campo_id']) ||
                !isset($filtro['valor'])
            ) {
                continue;
            }

            $campoId = $filtro['campo_id'];
            $operador = $filtro['operador'] ?? '=';
            $valor = $filtro['valor'];

            $query->whereHas('camposRespuestas', function ($q) use ($campoId, $operador, $valor) {

                $q->where('cf_id', $campoId);

                if ($operador === 'like') {

                    $q->where('valor', 'LIKE', "%{$valor}%");

                } elseif ($operador === 'not_like') {

                    $q->where('valor', 'NOT LIKE', "%{$valor}%");

                } else {

                    $q->where('valor', $operador, $valor);
                }
            });
        }

        $contador = $query->count();

        return [

            'tipo' => 'WID-010',

            'configuracion' => $config,

            'data' => [

                'contador' => $contador,

                'titulo' => $config['titulo']
                    ?? $widget->nombre,

                'descripcion' => $config['descripcion']
                    ?? null,

                'color' => $config['color']
                    ?? '#0d6efd',

                'icono' => $config['icono']
                    ?? 'fa-solid fa-hashtag',

                'prefijo' => $config['prefijo']
                    ?? null,

                'sufijo' => $config['sufijo']
                    ?? null,

                'mostrar_icono' =>
                    $config['mostrar_icono'] ?? true,

                'mostrar_descripcion' =>
                    $config['mostrar_descripcion'] ?? true,
            ]
        ];
    }
}
