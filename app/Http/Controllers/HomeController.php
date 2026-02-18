<?php

namespace App\Http\Controllers;

use App\Models\CamposForm;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\ConfiguracionCredenciales;
use App\Models\ContenedorGrid;
use App\Models\Formulario;
use App\Models\RespuestasCampo;
use App\Interfaces\FormularioInterface;
use Jenssegers\Agent\Agent;
class HomeController extends Controller
{


    protected $FormularioRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FormularioInterface $formularioInterface, )
    {
        $this->FormularioRepository = $formularioInterface;

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

        $contenedor = ContenedorGrid::with('filas.columnas.widget')
            ->whereHas('role', fn($q) => $q->where('name', $rolNombre))
            ->first();

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

        // Si no hay configuración, forzar cambio por seguridad
        if (!$config) {
            return 1;
        }

        $user = Auth::user();

        // Si nunca cambió su contraseña → forzar
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
                return [
                    'tipo' => 'WID-010',
                    'data' => [
                        'contador' => $widget->respuestas_count,
                        'nombre' => $widget->formulario->nombre ?? $widget->nombre,
                    ],
                ];

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
                $agent = new Agent();
                $isMobile = $agent->isMobile();
                $request = new Request();
                $resultado = $this->FormularioRepository->procesarFormularioConFiltros(Formulario::with('campos')->findOrFail($widget->formulario_id), $request);

                $campos = $resultado['formulario']->campos;

                return [
                    'tipo' => 'WID-004',
                    'data' => [
                        'isMobile' => $isMobile,
                        'campos' => $campos,
                        'resultado' => $resultado,

                    ],
                ];

            case 'WID-007': // Barra
            case 'WID-008': // Línea
            case 'WID-009': // Pastel
                return $this->resolverGrafico($widget);


            default:
                return [
                    'tipo' => 'DEFAULT',
                    'data' => [
                        'nombre' => $widget->nombre,
                    ],
                ];
        }
    }

    private function resolverGrafico($widget): array
    {
        $config = $widget->configuracion;

        $campoX = $config['campo_x_id'] ?? null;
        $campoY = $config['campo_y_id'] ?? null;
        $tipoGrafico = $widget->tipo;

        if (!$campoX) {
            return [
                'tipo' => $tipoGrafico,
                'data' => []
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO DE BARRA (WID-008)
        |--------------------------------------------------------------------------
        */
        if ($tipoGrafico === 'WID-008') {

            $query = RespuestasCampo::where('cf_id', $campoX);

            if ($campoY) {
                // SUMA
                $datos = $query
                    ->selectRaw('valor as label, SUM(CAST(valor as DECIMAL(10,2))) as total')
                    ->groupBy('valor')
                    ->pluck('total', 'label');
            } else {
                // CONTEO
                $datos = $query
                    ->selectRaw('valor as label, COUNT(*) as total')
                    ->groupBy('valor')
                    ->pluck('total', 'label');
            }

            return [
                'tipo' => $tipoGrafico,
                'data' => [
                    'labels' => $datos->keys(),
                    'values' => $datos->values(),
                    'titulo' => $config['titulo'] ?? 'Gráfico de Barra'
                ]
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO DE PASTEL (WID-009)
        |--------------------------------------------------------------------------
        */
        if ($tipoGrafico === 'WID-009') {

            $datos = RespuestasCampo::where('cf_id', $campoX)
                ->selectRaw('valor as label, COUNT(*) as total')
                ->groupBy('valor')
                ->pluck('total', 'label');

            return [
                'tipo' => $tipoGrafico,
                'data' => [
                    'labels' => $datos->keys(),
                    'values' => $datos->values(),
                    'titulo' => $config['titulo'] ?? 'Gráfico Pastel'
                ]
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | GRÁFICO DE LÍNEA (WID-007)
        |--------------------------------------------------------------------------
        */
        if ($tipoGrafico === 'WID-007') {

            $periodo = $config['periodo'] ?? 'mes';

            $formato = $periodo === 'anio'
                ? '%Y'
                : '%Y-%m';

            $datos = RespuestasCampo::where('cf_id', $campoY)
                ->selectRaw("DATE_FORMAT(created_at, '{$formato}') as label, COUNT(*) as total")
                ->groupBy('label')
                ->orderBy('label')
                ->pluck('total', 'label');

            return [
                'tipo' => $tipoGrafico,
                'data' => [
                    'labels' => $datos->keys(),
                    'values' => $datos->values(),
                    'titulo' => $config['titulo'] ?? 'Gráfico de Línea'
                ]
            ];
        }

        return [
            'tipo' => $tipoGrafico,
            'data' => []
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

            // 📅 Filtro por fecha
            if (($filtros['fecha'] ?? null) === 'anio_actual') {
                $query->whereYear('created_at', now()->year);
            }

            // 🔎 Filtro por otro campo
            if (
                !empty($filtros['campo']['cf_id']) &&
                !empty($filtros['campo']['valor'])
            ) {
                $query->whereHas('respuesta.camposRespuestas', function ($q) use ($filtros) {
                    $q->where('cf_id', $filtros['campo']['cf_id'])
                        ->where('valor', $filtros['campo']['valor']);
                });
            }

            // 📊 Tipo de estadística
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
}
