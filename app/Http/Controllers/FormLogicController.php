<?php

namespace App\Http\Controllers;

use App\Models\FormLogicRule;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormLogicInterface;
use App\Models\Modulo;
use App\Models\PlantillaCorreo;
use App\Models\User;
use Spatie\Permission\Models\Role;
class FormLogicController extends Controller
{

    protected $CatalogoRepository;
    protected $FormLogicRepository;


    public function __construct(CatalogoInterface $catalogoInterface, FormLogicInterface $formLogicInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormLogicRepository = $formLogicInterface;

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
            'plantillas'
        ));
    }

    public function store(Request $request, Modulo $modulo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'formulario_id' => 'required|exists:formularios,id',
            'evento' => 'required|string',
            'activo' => 'nullable',
            'acciones_json' => 'required|string',
        ]);


        $rule = $this->FormLogicRepository->CrearRegla($request);

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
            'actions.formularioDestino',
            'actions.conditions.campoCondicion',
            'actions.conditions.campoValor'
        ])->find($rule->id);


        $rule = (object) [
            'id' => $rule->id,
            'nombre' => $rule->nombre,
            'evento' => $rule->evento,
            'activo' => $rule->activo,
            'formulario' => $rule->formulario?->nombre ?? 'No asignado',
            'formulario_id' => $rule->formulario?->id ?? null,

            'acciones' => $rule->actions->map(function ($action) {

                $tipo = $action->tipo_accion; // TAC-001, TAC-005 o enviar_email
                $p = $action->parametros;     // JSON completo guardado
    
                /** ------------------------------
                 *  1️⃣ TAC-001 → modificar_campo
                 * ------------------------------*/
                if ($tipo === 'TAC-001') {
                    return (object) [
                        'id' => $action->id,
                        'tipo_accion_id' => $tipo,
                        'tipo_accion_text' => $action->tipo_accion_catalogo,

                        'form_ref_id' => $action->formularioDestino?->id,
                        'form_ref_text' => $action->formularioDestino?->nombre ?? 'No asignado',

                        'campo_ref_id' => $action->campoDestino?->id,
                        'campo_ref_text' => $action->campoDestino?->nombre ?? 'No asignado',

                        'operacion' => $p['operacion'] ?? null,
                        'operacion_text' => $action->operacion_catalogo,

                        'tipo_valor' => $p['tipo_valor'] ?? null,

                        'valor' => $p['valor'] ?? null,

                        'valor_text' => ($p['tipo_valor'] ?? null) === 'campo'
                            ? 'Campo "' . ($action->campoOrigen?->nombre ?? '---') . '" del formulario de origen'
                            : ('Valor estático "' . ($p['valor'] ?? '') . '"'),

                        'filtros_relacion' => $p['filtros_relacion'] ?? [],
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
            'plantillas'
        ));
    }

    public function update(Request $request, $form_logic, Modulo $modulo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'formulario_id' => 'required|exists:formularios,id',
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
