<?php

namespace App\Http\Controllers;

use App\Models\FormLogicRule;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormLogicInterface;
use App\Models\FormLogicExecution;
use App\Models\Modulo;
use App\Models\PlantillaCorreo;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class FormLogicController extends Controller
{

    protected $CatalogoRepository;
    protected $FormLogicRepository;


    public function __construct(CatalogoInterface $catalogoInterface, FormLogicInterface $formLogicInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormLogicRepository = $formLogicInterface;

    }

    public function indexTareas()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Tareas Programadas', 'url' => ''],
        ];
        $tareas = FormLogicRule::where('evento', 'scheduled')
            ->with('actions')
            ->orderBy('created_at', 'desc')
            ->get();

        $reglas = FormLogicRule::where('evento', 'scheduled')
            ->where('activo', true)
            ->with([
                'actions',
                'ejecuciones' => function ($q) {
                    $q->latest('inicio')->limit(1);
                }
            ])
            ->get();

        foreach ($reglas as $regla) {

            $this->FormLogicRepository->ejecutarTarea($regla);
        }

        dd(1);

        return view('tareas_programadas.index', compact('tareas', 'breadcrumb'));
    }
    public function historial(FormLogicRule $rule)
    {

        $historial = FormLogicExecution::where(
            'rule_id',
            $rule->id
        )
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($item) {


                return [

                    'inicio' => $item->inicio
                        ? $item->inicio->format('Y-m-d H:i:s')
                        : null,


                    'fin' => $item->fin
                        ? $item->fin->format('Y-m-d H:i:s')
                        : null,


                    'estado' => $item->estado,


                    'registros_afectados' =>
                        $item->registros_afectados,


                    'mensaje' => $item->mensaje,


                    'error' => $item->error,


                ];


            });


        return response()->json($historial);

    }

    public function create(Modulo $modulo)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],
            ['name' => 'Logica de Negocio', 'url' => ''],
        ];


        $formularios = $modulo->formularios;

        $operaciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);
        $tipo_acciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Tipos de Acción', true);
        $OpcionesCondiciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Opciones Condiciones', true);


        $usuarios = User::select('id', 'name', 'email')->get();
        $roles = Role::select('id', 'name')->get();
        $plantillas = PlantillaCorreo::where('estado', '1')->get();

        return view('form_logic.create', compact(
            'modulo',
            'tipo_acciones',
            'operaciones',
            'formularios',
            'breadcrumb',
            'usuarios',
            'roles',
            'plantillas',
            'OpcionesCondiciones'
        ));
    }

    public function store(Request $request, Modulo $modulo)
    {

        $request->validate([
            'nombre' => 'required|string|max:255',

            'evento' => 'required|string',

            'formulario_id_disparador' => [
                Rule::requiredIf($request->evento !== 'scheduled'),
                'nullable',
                'exists:formularios,id',
            ],

            'programacion.tipo' => [
                Rule::requiredIf($request->evento === 'scheduled'),
                'nullable',
                'in:once,daily,weekly,monthly,interval',
            ],

            'programacion.hora' => [
                Rule::requiredIf($request->evento === 'scheduled'),
                'nullable',
                'date_format:H:i',
            ],

            'programacion.inicio' => [
                Rule::requiredIf($request->evento === 'scheduled'),
                'nullable',
                'date',
            ],

            'programacion.fin' => 'nullable|date|after_or_equal:programacion.inicio',

            'acciones_json' => 'required|string',
        ]);

        $rule = $this->FormLogicRepository->CrearRegla($request, $modulo->id);
        return redirect()->route('modulo.administrar', $modulo->id)->with('success', 'Regla creada correctamente.');
    }


    public function edit(FormLogicRule $rule, Modulo $modulo)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],

            ['name' => 'Logica de Negocio', 'url' => ''],
        ];
        $operaciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);
        $tipo_acciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Tipos de Acción', true);

        $formularios = $modulo->formularios;


        $rule = FormLogicRule::with([
            'formulario',
            'actions',
            'actions',

        ])->find($rule->id);


        $rule = (object) [
            'id' => $rule->id,
            'nombre' => $rule->nombre,
            'evento' => $rule->evento,
            'activo' => $rule->activo,
            'segundo_plano' => $rule->segundo_plano,
            'formulario' => $rule->formulario?->nombre ?? 'No asignado',
            'formulario_id' => $rule->formulario?->id ?? null,

            'acciones' => $rule->actions->map(function ($action) {

                $tipo = $action->tipo_accion; // TAC-001, TAC-005 o enviar_email
                $p = $action->parametros;     // JSON completo guardado
    

                //TAC-001 → modificar_campo
    
                if ($tipo === 'TAC-001') {

                    return (object) [

                        'id' => $action->id,

                        'tipo_accion_id' => $tipo,

                        'tipo_accion_text' => $action->tipo_accion_catalogo,


                        'form_origen_id' => $p['form_origen_id'] ?? null,

                        'form_ref_id' => $p['form_ref_id'] ?? null,

                        'form_ref_text' => $p['form_ref_text']
                            ?? $action->formularioDestino?->nombre
                            ?? 'No asignado',



                        'asignaciones' => $p['asignaciones'] ?? [],



                        /*
                        |--------------------------------------------------------------------------
                        | Compatibilidad con acciones antiguas
                        |--------------------------------------------------------------------------
                        */

                        'campo_ref_id' => $p['campo_ref_id']
                            ?? $action->campoDestino?->id,

                        'campo_ref_text' => $p['campo_ref_text']
                            ?? $action->campoDestino?->nombre
                            ?? 'No asignado',

                        'operacion_rev' => $p['operacion_rev'] ?? 0,

                        'operacion' => $p['operacion'] ?? null,

                        'operacion_text' => $p['operacion_text']
                            ?? $action->operacion_catalogo,

                        'tipo_valor' => $p['tipo_valor'] ?? null,

                        'valor' => $p['valor'] ?? null,

                        'valor_text' => ($p['tipo_valor'] ?? null) === 'campo'

                            ? ($p['valor_text'] ?? '---')

                            : ('Valor estático "' . ($p['valor'] ?? '') . '"'),

                        'filtros_relacion' => $p['filtros_relacion'] ?? [],

                        'condiciones' => $p['condiciones'] ?? [],

                    ];
                }
                /** ------------------------------
                 *  3️⃣ enviar_email
                 * ------------------------------*/
                if ($tipo === 'TAC-003') {

                    $usuarios = $p['email_usuarios'] ?? [];
                    $roles = $p['email_roles'] ?? [];



                    // 🔹 Textos visibles (puedes mejorar luego con consultas reales)
    
                    $usuariosText = User::whereIn('id', $usuarios)
                        ->get()
                        ->pluck('name_email')
                        ->toArray();

                    $rolesText = Role::whereIn('id', $roles)
                        ->pluck('name')
                        ->toArray();

                    return (object) [
                        'id' => $action->id,

                        'tipo_accion_id' => $tipo,
                        'tipo_accion_text' => 'enviar_email',

                        'form_ref_id' => '',
                        'filtros_relacion' => [],

                        // Datos directos
                        'email_subject' => $p['email_subject'] ?? '',
                        'email_body' => $p['email_body'] ?? '',
                        'email_template' => $p['email_template'] ?? null,
                        'email_usuarios' => $usuarios,
                        'email_roles' => $roles,

                        // LO QUE EL JS LEE
                        'email_detalle' => [
                            'to' => $usuarios,
                            'to_text' => $usuariosText,
                            'roles' => $roles,
                            'roles_text' => $rolesText,
                            'subject' => $p['email_subject'] ?? '',
                            'body' => $p['email_body'] ?? '',
                            'template' => $p['email_template'] ?? null,

                            // opcional pero recomendado
                            'camposUsados' => $p['camposUsados'] ?? [],
                        ],

                        'condiciones' => $p['condiciones'] ?? [],
                    ];
                }
                /** ------------------------------
                 *  2️⃣ TAC-005 → crear_registros
                 * ------------------------------*/
                if ($tipo === 'TAC-005') {
                    return (object) [
                        'id' => $action->id,
                        'tipo_accion_id' => $tipo,
                        'tipo_accion_text' => $action->tipo_accion_catalogo,

                        'form_ref_id' => $action->formularioDestino?->id,
                        'form_ref_text' => $action->formularioDestino?->nombre ?? 'No asignado',

                        'usar_relacion' => $p['usar_relacion'] ?? false,
                        'tipo_accion_text_raw' => $p['tipo_accion_text'] ?? '',

                        'formulario_relacion_seleccionado' => $p['formulario_relacion_seleccionado'] ?? null,
                        'formulario_relacion_text' => $p['formulario_relacion_text'] ?? '',

                        'campos' => $p['campos'] ?? [],

                        'filtros_relacion' => $p['filtros_relacion'] ?? [],
                        'condiciones' => $p['condiciones'] ?? [],
                    ];
                }

                if ($tipo === 'TAC-006') {
                    return (object) [
                        'id' => $action->id,
                        'tipo_accion_id' => $tipo,
                        'tipo_accion_text' => $action->tipo_accion_catalogo,

                        'form_origen_id' => $p['form_origen_id'],
                        'form_origen_text' => $action->formularioOrigen?->nombre ?? 'No asignado',

                        'form_ref_id' => $p['form_ref_id'],
                        'form_ref_text' => $action->formularioDestino?->nombre ?? 'No asignado',

                        'condiciones' => $p['condiciones'] ?? [],
                    ];
                }



                /** ------------------------------
                 * 4️⃣ Otros tipos (fallback)
                 * ------------------------------*/
                return (object) [
                    'id' => $action->id,
                    'tipo_accion_id' => $tipo,
                    'tipo_accion_text' => $action->tipo_accion_catalogo,
                    'parametros' => $p,
                ];
            }),
        ];


        $usuarios = User::select('id', 'name', 'email')->get();
        $roles = Role::select('id', 'name')->get();
        $plantillas = PlantillaCorreo::where('estado', '1')->get();

        $OpcionesCondiciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Opciones Condiciones', true);




        //dd($rule);
        return view('form_logic.edit', compact(
            'modulo',
            'tipo_acciones',
            'operaciones',
            'rule',
            'formularios',
            'breadcrumb',
            'usuarios',
            'roles',
            'plantillas',
            'OpcionesCondiciones'
        ));
    }

    public function update(Request $request, $form_logic, Modulo $modulo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'formulario_id_disparador' => 'required|exists:formularios,id',
            'evento' => 'required|string',
            'activo' => 'nullable',
            'acciones_json' => 'required|string',
        ]);

        $rule = $this->FormLogicRepository->EditarRegla($request, $form_logic);


        return redirect()->route('modulo.administrar', $modulo->id)->with('success', 'Regla actualizada correctamente.');
    }


    public function destroy(FormLogicRule $rule)
    {
        $rule->delete();
        return redirect()->back()->with('status', 'Regla eliminada correctamente.');
    }
}
