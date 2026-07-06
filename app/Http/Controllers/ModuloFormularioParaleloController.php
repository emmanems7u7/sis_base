<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\FormularioAsociacion;
use Illuminate\Http\Request;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\CamposFormInterface;
use App\Models\Formulario;

class ModuloFormularioParaleloController extends Controller
{


    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $CamposFormRepository;

    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        CamposFormInterface $CamposFormInterface,

    ) {
        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->CamposFormRepository = $CamposFormInterface;


    }

    public function create($form_principal, $form_relacion)
    {
        // Buscar si ya existe una asociación con exactamente estos formularios
        $asociacion = FormularioAsociacion::query()
            ->whereJsonContains('formularios', ['id' => $form_principal])
            ->whereJsonContains('formularios', ['id' => $form_relacion])
            ->first();

        // Si no existe, crearla
        if (!$asociacion) {

            $asociacion = FormularioAsociacion::create([
                'formularios' => [
                    [
                        'id' => $form_principal,
                        'es_principal' => 1,
                    ],
                    [
                        'id' => $form_relacion,
                        'es_principal' => 0,
                    ],
                ],
                'config' => [],
            ]);
        }

        // Formularios seleccionados
        $seleccionados = collect($asociacion->formularios)
            ->pluck('id')
            ->toArray();

        // Formulario principal
        $principal = collect($asociacion->formularios)
            ->firstWhere('es_principal', 1)['id'] ?? null;
        // Obtener formularios con sus campos
        $formularios = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])
            ->whereIn('id', $seleccionados)
            ->get()
            ->sortByDesc(fn($form) => $form->id == $principal)
            ->values();

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Campos', 'url' => route('formularios.campos.index', $principal)],
            ['name' => 'Reglas de relación', 'url' => ''],
        ];

        return view('modulos.agrupacion.create', [
            'asociacion' => $asociacion,
            'formularios' => $formularios,
            'seleccionados' => $seleccionados,
            'principal' => $principal,
            'grupoNombre' => null,
            'breadcrumb' => $breadcrumb,
        ]);
    }
    public function store(Request $request, $modulo)
    {
        $request->validate([
            'grupo' => 'required|string',
            'formularios' => 'required|array|min:1',
            'principal' => 'required|in:' . implode(',', $request->formularios),
            'operaciones_json' => 'required|string',

        ]);


        $operaciones = json_decode($request->operaciones_json, true);
        if ($operaciones === null) {
            return back()->withErrors(['operaciones_json' => 'Formato de operaciones inválido.']);
        }

        foreach ($operaciones as $opIndex => $operacion) {
            // Validar destino
            if (empty($operacion['destino']['tipo']) || empty($operacion['destino']['nombre']) || empty($operacion['destino']['campo_id'])) {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " tiene un destino incompleto."
                ]);
            }

            $formula = $operacion['formula'] ?? [];
            if (count($formula) === 0) {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " no tiene fórmula."
                ]);
            }

            // Validar que haya al menos un operador


            // Validar que no termine con operador
            $ultimo = end($formula);
            if (isset($ultimo['tipo']) && $ultimo['tipo'] === 'operador') {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " no puede terminar con un operador."
                ]);
            }


            $formularios = $request->formularios;
            $principal = $request->principal;
            $config = json_decode($request->operaciones_json, true);

            $formulariosData = [];

            foreach ($formularios as $formId) {
                $formulariosData[] = [
                    'id' => $formId,
                    'es_principal' => $principal == $formId ? 1 : 0
                ];
            }


        }

        FormularioAsociacion::create([
            'modulo_id' => $modulo,
            'grupo' => $request->grupo,
            'formularios' => $formulariosData,
            'config' => $config ?? null
        ]);
        return redirect()->route('modulo.administrar', $modulo)->with('status', 'Grupo creado');
    }

    public function edit($grupo, $modulo)
    {
        $modulo = Modulo::with('formularios')->findOrFail($modulo);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],
            ['name' => 'Asociacion de formularios', 'url' => ''],
        ];

        $grupoData = FormularioAsociacion::where('modulo_id', $modulo->id)
            ->where('id', $grupo)
            ->first();

        $formulariosDecode = $grupoData->formularios ?? '[]';

        // Obtener IDs seleccionados
        $seleccionados = collect($formulariosDecode)->pluck('id')->toArray();

        // Obtener principal
        $principal = collect($formulariosDecode)
            ->firstWhere('es_principal', 1)['id'] ?? null;

        return view('modulos.agrupacion.edit', [
            'modulo' => $modulo,
            'formularios' => $modulo->formularios,
            'seleccionados' => $seleccionados,
            'principal' => $principal,
            'grupoNombre' => $grupoData->grupo,
            'grupoId' => $grupoData->id,
            'breadcrumb' => $breadcrumb,
            'configOperaciones' => $grupoData->config ?? '[]',
        ]);
    }

    public function update(Request $request, $grupo, $modulo)
    {
        $request->validate([
            'grupo' => 'required|string',
            'formularios' => 'required|array|min:1',
            'principal' => 'required|in:' . implode(',', $request->formularios),
            'operaciones_json' => 'required|string',
        ]);

        $operaciones = json_decode($request->operaciones_json, true);

        if ($operaciones === null) {
            return back()->withErrors(['operaciones_json' => 'Formato de operaciones inválido.']);
        }

        foreach ($operaciones as $opIndex => $operacion) {

            if (empty($operacion['destino']['tipo']) || empty($operacion['destino']['nombre']) || empty($operacion['destino']['campo_id'])) {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " tiene un destino incompleto."
                ]);
            }

            $formula = $operacion['formula'] ?? [];

            if (count($formula) === 0) {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " no tiene fórmula."
                ]);
            }

            $ultimo = end($formula);
            if (isset($ultimo['tipo']) && $ultimo['tipo'] === 'operador') {
                return back()->withErrors([
                    'operaciones_json' => "La operación #" . ($opIndex + 1) . " no puede terminar con un operador."
                ]);
            }
        }

        $registro = FormularioAsociacion::where('modulo_id', $modulo)
            ->where('id', $grupo)
            ->first();

        if (!$registro) {
            return back()->withErrors(['error' => 'No se encontró el grupo a actualizar']);
        }

        $formularios = $request->formularios;
        $principal = $request->principal;

        $formulariosData = [];

        foreach ($formularios as $formId) {
            $formulariosData[] = [
                'id' => $formId,
                'es_principal' => $principal == $formId ? 1 : 0
            ];
        }

        $registro->update([
            'grupo' => $request->grupo,
            'formularios' => $formulariosData,
            'config' => $operaciones
        ]);

        return redirect()->back()->with('status', 'Grupo actualizado');
    }
    public function destroy($modulo, $grupo)
    {

        FormularioAsociacion::where('modulo_id', $modulo)
            ->where('id', $grupo)->delete();


        return redirect()->back()->with('status', 'Grupo eliminado');
    }

    public function camposMultiples(Request $request)
    {
        $request->validate([
            'formularios' => 'required|array|min:1',
            'principal' => 'required|in:' . implode(',', $request->formularios),
        ]);

        $formularios = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])->whereIn('id', $request->formularios)->get();

        $formularios = $formularios->sortByDesc(function ($form) use ($request) {
            return $form->id == $request->principal;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $formularios
        ]);
    }

}
