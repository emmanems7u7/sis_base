<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\FormLogicRule;
use App\Models\FormLogicAction;
use App\Models\FormLogicCondition;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;
use App\Models\Modulo;

class FormLogicController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $catalogoInterface, )
    {

        $this->CatalogoRepository = $catalogoInterface;

    }


    public function create(Modulo $modulo)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],
            ['name' => 'Logica de Negocio', 'url' => route('correos.index')],
        ];


        $formularios = $modulo->formularios;

        $operaciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);
        $tipo_acciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Tipos de Acción', true);

        return view('form_logic.create', compact('modulo', 'tipo_acciones', 'operaciones', 'formularios', 'breadcrumb'));
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
        $acciones = json_decode($request->acciones_json, true);
        //dd($acciones);

        // Crear regla
        $rule = FormLogicRule::create([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);
        //dd($acciones);
        $this->guardarAccionesYCondiciones($rule, $acciones);

        return redirect()->route('modulo.administrar', $modulo->id)->with('success', 'Regla creada correctamente.');
    }


    public function edit(FormLogicRule $rule, Modulo $modulo)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],

            ['name' => 'Logica de Negocio', 'url' => route('correos.index')],
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
                if ($tipo === 'enviar_email') {
                    return (object) [
                        'id' => $action->id,
                        'tipo_accion_id' => $tipo,
                        'tipo_accion_text' => 'Enviar correo',

                        'email_to' => $p['email_to'] ?? '',
                        'email_subject' => $p['email_subject'] ?? '',
                        'email_body' => $p['email_body'] ?? '',

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


        //dd($rule);
        return view('form_logic.edit', compact('modulo', 'tipo_acciones', 'operaciones', 'rule', 'formularios', 'breadcrumb'));
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

        $acciones = json_decode($request->acciones_json, true);
        $form_logic = FormLogicRule::findOrFail($form_logic);
        $form_logic->update([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);

        // Eliminar acciones existentes y sus condiciones
        $form_logic->actions()->delete();

        $this->guardarAccionesYCondiciones($form_logic, $acciones);

        return redirect()->route('modulo.administrar', $modulo->id)->with('success', 'Regla actualizada correctamente.');
    }

    // Función para guardar acciones y condiciones
    protected function guardarAccionesYCondiciones(FormLogicRule $rule, array $acciones)
    {


        foreach ($acciones as $actionData) {
            // Preparamos los parámetros extra según el tipo de acción
            $parametrosExtra = [];

            switch ($actionData['tipo_accion_id']) {
                case 'TAC-001': // modificar_campo
                    $parametrosExtra = [

                        'operacion' => $actionData['operacion'] ?? 'actualizar',
                        'tipo_valor' => $actionData['tipo_valor'] ?? 'static',
                        'valor' => $actionData['valor'] ?? null,
                        'valor_text' => $actionData['valor_text'] ?? null,
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
                        'campo_ref_id' => $actionData['campo_ref_id'] ?? [],
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? [],
                        'form_ref_text' => $actionData['form_ref_text'] ?? [],
                        'campo_ref_text' => $actionData['campo_ref_text'] ?? [],
                        'operacion_text' => $actionData['operacion_text'] ?? [],
                        'condiciones' => $actionData['condiciones'] ?? [],


                    ];
                    break;


                case 'TAC-005': // crear_registros
                    $parametrosExtra = [
                        'usar_relacion' => $actionData['usar_relacion'] ?? false,
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? '',
                        'formulario_relacion_seleccionado' => $actionData['formulario_relacion_seleccionado'] ?? null,
                        'formulario_relacion_text' => $actionData['formulario_relacion_text'] ?? '',
                        'campos' => $actionData['campos'] ?? [],
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
                        'condiciones' => $actionData['condiciones'] ?? [],
                    ];
                    break;

                case 'enviar_email':
                    $parametrosExtra = [
                        'email_to' => $actionData['email_to'] ?? null,
                        'email_subject' => $actionData['email_subject'] ?? null,
                        'email_body' => $actionData['email_body'] ?? null,
                        'condiciones' => $actionData['condiciones'] ?? [],
                    ];
                    break;

                default:
                    // Para otros tipos de acción simplemente guardamos todo el actionData
                    $parametrosExtra = $actionData;
                    break;
            }
            //dump($actionData);
            // Creamos el registro en FormLogicAction
            $action = FormLogicAction::create([
                'rule_id' => $rule->id,
                'form_ref_id' => $actionData['form_ref_id'] ?? null,
                'tipo_accion' => $actionData['tipo_accion_id'] ?? '',
                'parametros' => $parametrosExtra, // cast array/json en el modelo
            ]);
        }
        //dd($acciones);
    }

    public function destroy(FormLogicRule $rule)
    {
        $rule->delete();
        return redirect()->back()->with('status', 'Regla eliminada correctamente.');
    }
}
