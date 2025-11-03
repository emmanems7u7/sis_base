<?php

namespace App\Http\Controllers;

use App\Models\Formulario;
use App\Models\FormLogicRule;
use App\Models\FormLogicAction;
use App\Models\FormLogicCondition;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;

class FormLogicController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $catalogoInterface, )
    {

        $this->CatalogoRepository = $catalogoInterface;

    }
    public function index()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Logica de Negocio', 'url' => route('correos.index')],
        ];
        $rules = FormLogicRule::with('formulario', 'actions.formularioDestino')->get();

        $rules->operacion = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);
        return view('form_logic.index', compact('rules', 'breadcrumb'));
    }

    public function create()
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Logica de Negocio', 'url' => route('correos.index')],
        ];
        $formularios = Formulario::all();

        $operaciones = $this->CatalogoRepository->obtenerCatalogosPorCategoria('Operaciones de Campo', true);

        return view('form_logic.create', compact('operaciones', 'formularios', 'breadcrumb'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'nombre' => 'required|string|max:255',
            'formulario_id' => 'required|exists:formularios,id',
            'evento' => 'required|string',

            'actions' => 'array',
        ]);

        // Crear regla
        $rule = FormLogicRule::create([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);

        $this->guardarAccionesYCondiciones($rule, $request->actions ?? []);

        return redirect()->route('form-logic.index')->with('success', 'Regla creada correctamente.');
    }

    public function edit(FormLogicRule $rule)
    {

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Logica de Negocio', 'url' => route('correos.index')],
        ];

        $formularios = Formulario::all();
        $rule->load('actions.conditions');
        return view('form_logic.edit', compact('rule', 'formularios', 'breadcrumb'));
    }

    public function update(Request $request, FormLogicRule $form_logic)
    {


        $request->validate([
            'nombre' => 'required|string|max:255',
            'form_id' => 'required|exists:formularios,id',
            'evento' => 'required|string',
            'activo' => 'boolean',
            'actions' => 'array',
        ]);

        $form_logic->update([
            'nombre' => $request->nombre,
            'form_id' => $request->form_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);

        // Eliminar acciones existentes y sus condiciones
        $form_logic->actions()->delete();

        $this->guardarAccionesYCondiciones($form_logic, $request->actions ?? []);


        return redirect()->route('form-logic.index')->with('success', 'Regla actualizada correctamente.');
    }

    // Función para guardar acciones y condiciones
    protected function guardarAccionesYCondiciones(FormLogicRule $rule, array $acciones)
    {
        foreach ($acciones as $actionData) {
            $valor = null;
            if (isset($actionData['tipo_valor'])) {
                if ($actionData['tipo_valor'] === 'campo') {
                    $valor = $actionData['valor_campo'] ?? null;
                } else {
                    $valor = $actionData['valor'] ?? null;
                }
            }

            $action = FormLogicAction::create([
                'rule_id' => $rule->id,
                'form_ref_id' => $actionData['form_ref_id'] ?? null,
                'campo_ref_id' => $actionData['campo_ref_id'] ?? null,
                'operacion' => $actionData['operacion'] ?? 'actualizar',
                'valor' => $valor,
                'parametros' => $actionData['parametros'] ?? null,
                'tipo_valor' => $actionData['tipo_valor'] ?? 'static',
            ]);

            if (!empty($actionData['conditions']) && is_array($actionData['conditions'])) {
                $condicionCompleta = [];

                // Reconstruir cada condición combinando los arrays separados
                foreach ($actionData['conditions'] as $cond) {
                    foreach ($cond as $key => $value) {
                        $condicionCompleta[$key] = $value;
                    }
                }
                dd($actionData['conditions']);
                // Guardar condición
                FormLogicCondition::create([
                    'action_id' => $action->id,
                    'campo_condicion' => $condicionCompleta['campo_condicion_origen'] ?? null,
                    'operador' => $condicionCompleta['operador'] ?? '=',
                    'valor' => $condicionCompleta['campo_condicion_destino'] ?? null,
                ]);
            }
        }
    }

    public function destroy(FormLogicRule $rule)
    {
        $rule->delete();
        return redirect()->route('form-logic.index')->with('success', 'Regla eliminada correctamente.');
    }
}
