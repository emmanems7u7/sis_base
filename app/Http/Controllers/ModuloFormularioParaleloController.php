<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\ModuloFormularioParalelo;
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

    public function create($modulo)
    {

        $modulo = Modulo::with('formularios')->findOrFail($modulo);

        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrar Módulo', 'url' => route('modulo.administrar', $modulo->id)],
            ['name' => 'Asociacion de formularios', 'url' => ''],
        ];

        $grupos = ModuloFormularioParalelo::where('modulo_id', $modulo->id)->get();
        //Se agrega filtro de formularios usados en grupos para no mostrarlos en la creación de nuevos grupos
        //Para evitar ambigüedades en la selección de formularios para cada grupo
        $idsUsados = $grupos->flatMap(function ($grupo) {
            return collect($grupo->formularios)->pluck('id');
        })->unique()->toArray();


        $formulariosDisponibles = $modulo->formularios->whereNotIn('id', $idsUsados);

        return view('modulos.agrupacion.create', [
            'modulo' => $modulo,
            'formularios' => $formulariosDisponibles,
            'seleccionados' => [],
            'principal' => null,
            'grupoNombre' => null,
            'breadcrumb' => $breadcrumb
        ]);
    }

    // 🔹 GUARDAR
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

            ModuloFormularioParalelo::create([
                'modulo_id' => $modulo,
                'grupo' => $request->grupo,
                'formularios' => $formulariosData,
                'config' => $config ?? null
            ]);
        }
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

        $grupoData = ModuloFormularioParalelo::where('modulo_id', $modulo->id)
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

    // 🔹 ACTUALIZAR
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

        $registro = ModuloFormularioParalelo::where('modulo_id', $modulo)
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

    // 🔹 ELIMINAR
    public function destroy($modulo, $grupo)
    {
        ModuloFormularioParalelo::where('modulo_id', $modulo)
            ->where('grupo', $grupo)
            ->delete();

        return redirect()->back()->with('success', 'Grupo eliminado');
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
