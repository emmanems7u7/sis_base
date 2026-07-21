<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\RespuestasForm;

use Illuminate\Support\Facades\DB;

use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\FormLogicInterface;
use App\Interfaces\RespuestasFormInterface;
use App\Interfaces\CamposFormInterface;
use App\Interfaces\RespuestasCampoInterface;
use App\Interfaces\ModuloInterface;


class RespuestasFormController extends Controller
{


    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $FormLogicInterface;
    protected $RespuestasFormInterface;
    protected $CamposFormRepository;
    protected $RespuestasCampoRepository;
    protected $ModuloRepository;

    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        FormLogicInterface $formLogicInterface,
        RespuestasFormInterface $respuestasFormInterface,
        CamposFormInterface $camposFormInterface,
        RespuestasCampoInterface $respuestasCampoInterface,
        ModuloInterface $moduloInterface
    ) {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->FormLogicInterface = $formLogicInterface;
        $this->RespuestasFormInterface = $respuestasFormInterface;
        $this->CamposFormRepository = $camposFormInterface;
        $this->RespuestasCampoRepository = $respuestasCampoInterface;
        $this->ModuloRepository = $moduloInterface;

    }
    public function index()
    {
        $respuestas = RespuestasForm::with(['camposRespuestas.campo', 'actor', 'formulario'])->get();

        return view('formularios.respuestas', compact('respuestas'));
    }

    public function indexPorFormulario(Request $request, $form)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas', 'url' => route('formularios.index')],
        ];

        $resultado = $this->FormularioRepository->procesarFormularioConFiltros($this->FormularioRepository->GetFormRelacion($form, 'campos'), $request);

        $campos = $resultado['formulario']->campos;

        DB::disconnect();
        return view('formularios.respuestas_formulario', array_merge($resultado, compact('breadcrumb', 'campos')));
    }




    public function create($form, $modulo)
    {
        $formularioModelo = $this->FormularioRepository->GetFormById($form);
        $moduloModelo = $modulo > 0 ? $this->ModuloRepository->GetModuloById($modulo) : null;

        $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

        if ($moduloModelo) {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Módulo ' . $moduloModelo->nombre, 'url' => route('modulo.index', $moduloModelo->id)],
                ['name' => 'Registrar ' . $formularioModelo->nombre, 'url' => route('permissions.index')],
            ];
        } else {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Formularios', 'url' => route('formularios.index')],
                ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $formularioModelo->id)],
                ['name' => 'Registrar Datos', 'url' => route('permissions.index')],
            ];
        }

        $formulariosFinales = collect();
        $formulas = [];

        if ($moduloModelo) {

            $grupoData = $this->FormularioRepository->obtenerFormulariosDelGrupo($form, $moduloModelo->id);

            $formulas = $grupoData['grupo']->config ?? [];
            if ($grupoData && $grupoData['principal_id'] == $form) {

                foreach ($grupoData['formularios'] as $f) {
                    $formulariosFinales->push($this->RespuestasFormInterface->cargarFormularioCompleto($f['id']));
                }

            } else {
                $formulariosFinales->push($this->RespuestasFormInterface->cargarFormularioCompleto($form));
            }

        } else {
            $formulariosFinales->push($this->RespuestasFormInterface->cargarFormularioCompleto($form));
        }

        $humanRules = collect();

        foreach ($formulariosFinales as $formulario) {
            $humanRules = $humanRules->merge(
                $this->RespuestasFormInterface->obtenerReglasHumanas($formulario->campos)
            );
        }

        return view('formularios.registrar_datos_form', [
            'formularios' => $formulariosFinales,
            'formulario' => $formulariosFinales->first(),
            'humanRules' => $humanRules,
            'breadcrumb' => $breadcrumb,
            'moduloModelo' => $moduloModelo,
            'modulo' => $modulo,
            'formulas' => $formulas,
            'edit' => 0
        ]);
    }


    public function store(Request $request, $form, $modulo, $tipo)
    {
        $evento = 'on_create';
        DB::beginTransaction();
        try {

            $moduloModelo = $modulo > 0 ? $this->ModuloRepository->GetModuloById($modulo) : null;
            $formularioModelo = $this->FormularioRepository->GetFormById($form);

            $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

            $formularios = $this->FormularioRepository->obtenerFormularios($form, $moduloModelo);
            $respuestasCreadas = [];

            $registrosRaw = json_decode($request->registros_json, true);

            if (!is_array($registrosRaw)) {
                $registrosRaw = [];
            }

            $registros = $this->RespuestasFormInterface->normalizarRegistros($registrosRaw);

            if (!empty($registros)) {


                $grupo = null;

                if (count($registros) > 1) {
                    $grupo = $this->FormularioRepository->CrearRespuestaGrupo();
                }

                foreach ($registros as $registro) {

                    $formulariosEnRegistro = collect($registro)->keys()->map(fn($key) => explode('[', $key)[0])->unique();

                    foreach ($formulariosEnRegistro as $formPrefix) {

                        if (!is_string($formPrefix)) {
                            continue;
                        }

                        $formId = (int) str_replace('form_', '', $formPrefix);


                        $formularioModelo = $formularios->firstWhere('id', $formId);
                        if (!$formularioModelo)
                            continue;

                        $multiple = $formularioModelo->config['registro_multiple'] ?? false;

                        //SOLO múltiples
                        if (!$multiple)
                            continue;

                        $campos = $this->CamposFormRepository->GetCamposByForm($formId);

                        $datosFormulario = collect($registro)->filter(fn($value, $key) => str_starts_with($key, $formPrefix))->toArray();


                        $rules = $this->RespuestasFormInterface->validacion($formularioModelo, $campos, null, 'store', $formPrefix);

                        $resultado = $this->FormularioRepository->GetData($request, $formPrefix, $rules, $registro);

                        $datosFormulario = $resultado['datosFormulario'];

                        $validator = $resultado['validator'];


                        if ($validator->fails()) {
                            DB::rollBack();
                            return back()->withErrors($validator)->withInput();
                        }

                        try {

                            $res = $this->RespuestasFormInterface->procesarFormularioMultipleDesdeArray(
                                $datosFormulario,
                                $formId,
                                $campos,
                                $formPrefix,
                                $grupo,
                                $evento
                            );

                            $respuestasCreadas = array_merge($respuestasCreadas, $res);

                        } catch (\Exception $e) {
                            DB::rollBack();
                            return back()->withErrors($e->getMessage())->withInput();

                        }
                    }
                }
            }

            foreach ($formularios as $formularioModelo) {

                $formId = $formularioModelo->id;
                $formPrefix = "form_{$formId}";
                $multiple = $formularioModelo->config['registro_multiple'] ?? false;

                //SOLO normales
                if ($multiple)
                    continue;

                if (!$request->has($formPrefix))
                    continue;

                $campos = $this->CamposFormRepository->GetCamposByForm($formId);

                try {

                    $rules = $this->RespuestasFormInterface->validacion($formularioModelo, $campos, null, 'store', $formPrefix);

                    $resultado = $this->FormularioRepository->GetData($request, $formPrefix, $rules);

                    $datosFormulario = $resultado['datosFormulario'];
                    $validator = $resultado['validator'];

                    if ($validator->fails()) {
                        DB::rollBack();
                        return back()->withErrors($validator)->withInput();
                    }

                    $errores = $this->CatalogoRepository->validarOpcionesCatalogo($campos, $datosFormulario, $formPrefix);

                    if (!empty($errores)) {
                        DB::rollBack();
                        return back()->withErrors($errores)->withInput();
                    }


                    $respuestasCreadas[] = $this->RespuestasFormInterface->procesarFormularioNormalDesdeArray(
                        $datosFormulario,
                        $formId,
                        $campos,
                        $formPrefix,
                        $evento
                    );

                } catch (\Exception $e) {
                    DB::rollBack();
                    return back()->withErrors($e->getMessage())->withInput();

                }
            }

            $agrupadas = collect($respuestasCreadas)->groupBy(function ($item) {
                return is_array($item)
                    ? ($item['form_id'] ?? null)
                    : ($item->form_id ?? null);
            });

            $this->FormLogicInterface->EjecutarAcciones($agrupadas, $evento);

            DB::commit();
            DB::disconnect();

            if ($tipo == 0) {

                if ($moduloModelo) {

                    return redirect()->route('modulo.index', $moduloModelo->id)
                        ->with([
                            'status' => configForm($form, 'messages.success_create'),
                            'formulario_id' => $formularioModelo->id
                        ]);

                } else {

                    return redirect()
                        ->route('formularios.respuestas.formulario', $form)
                        ->with('status', configForm($form, 'messages.success_create'));
                }

            } else {

                return redirect()
                    ->route('home')
                    ->with('status', configForm($form, 'messages.success_create'));
            }
        } catch (\Exception $e) {

            DB::rollBack();
            DB::disconnect();

            throw $e;
        }
    }




    public function edit(RespuestasForm $respuesta, $modulo)
    {
        $formularioModelo = $respuesta->formulario()->first();
        $moduloModelo = $modulo > 0 ? $this->ModuloRepository->GetModuloById($modulo) : null;

        $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);


        if ($moduloModelo) {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Módulo ' . $moduloModelo->nombre, 'url' => route('modulo.index', $moduloModelo->id)],
                ['name' => 'Editar ' . $formularioModelo->nombre, 'url' => route('permissions.index')],
            ];
        } else {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Formularios', 'url' => route('formularios.index')],
                ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $formularioModelo->id)],
                ['name' => 'Editar Datos', 'url' => route('permissions.index')],
            ];
        }

        // cargar formularios
        $formulariosFinales = collect();
        $formulas = [];

        if ($moduloModelo) {

            $grupoData = $this->FormularioRepository->obtenerFormulariosDelGrupo($formularioModelo->id, $moduloModelo->id);

            $formulas = $grupoData['grupo']->config ?? [];

            if ($grupoData && $grupoData['principal_id'] == $formularioModelo->id) {

                foreach ($grupoData['formularios'] as $f) {
                    $formulariosFinales->push(
                        $this->RespuestasFormInterface->cargarFormularioCompleto($f['id'])
                    );
                }

            } else {
                $formulariosFinales->push(
                    $this->RespuestasFormInterface->cargarFormularioCompleto($formularioModelo->id)
                );
            }

        } else {
            $formulariosFinales->push(
                $this->RespuestasFormInterface->cargarFormularioCompleto($formularioModelo->id)
            );
        }

        // reglas humanas
        $humanRules = collect();

        foreach ($formulariosFinales as $formulario) {
            $humanRules = $humanRules->merge(
                $this->RespuestasFormInterface->obtenerReglasHumanas($formulario->campos)
            );
        }

        // obtener grupo o fallback
        $grupo = $respuesta->grupos()->with('respuestas.camposRespuestas')->first();

        if ($grupo) {
            $respuestas = $grupo->respuestas;
        } else {
            $respuesta->load('camposRespuestas');
            $respuestas = collect([$respuesta]);
        }

        // inicializar estructuras
        $valoresGlobal = [];

        foreach ($formulariosFinales as $form) {
            $form->registros_json = '[]';
            $valoresGlobal[$form->id] = [];
        }

        // REGISTROS MULTIPLES
        foreach ($respuestas as $res) {

            $formId = $res->form_id;

            $formularioTarget = $formulariosFinales->firstWhere('id', $formId);
            if (!$formularioTarget)
                continue;

            $multiple = $formularioTarget->config['registro_multiple'] ?? false;

            if (!$multiple)
                continue;

            $item = [];

            foreach ($res->camposRespuestas as $campoResp) {
                $campo = $this->CamposFormRepository->GetCampo($campoResp->cf_id);
                if (!$campo)
                    continue;

                $prefix = "form_{$formId}";
                $key = "{$prefix}[{$campo->id}]";

                $item[$key] = [
                    'value' => $campoResp->valor,
                    'text' => $this->FormularioRepository->obtenerValorReal($campo, $campoResp->valor)
                ];
            }

            $current = json_decode($formularioTarget->registros_json, true);
            $current[] = $item;
            $formularioTarget->registros_json = json_encode($current);
        }

        // FORMULARIOS NORMALES
        foreach ($formulariosFinales as $formularioTarget) {

            $esMultiple = $formularioTarget->config['registro_multiple'] ?? false;

            if ($esMultiple)
                continue;

            foreach ($formularioTarget->campos as $campo) {

                $valores = $respuestas
                    ->where('form_id', $formularioTarget->id)
                    ->flatMap(function ($res) use ($campo) {
                        return $res->camposRespuestas
                            ->where('cf_id', $campo->id)
                            ->pluck('valor');
                    })
                    ->toArray();

                $valoresGlobal[$formularioTarget->id][$campo->id] = $valores;
            }
        }

        // RELACION 1:N

        if (!empty($formulas)) {

            $formula = collect($formulas)->firstWhere('relacion_multiple', 1);

            if ($formula) {

                $campoDestino = $formula['destino']['campo_id'] ?? null;
                $campoOrigen = $formula['formula'][1]['campo_id'] ?? null;
                $formOrigenId = $formula['formula'][1]['form'] ?? null;

                $valorClave = null;

                foreach ($respuestas as $res) {

                    $match = $res->camposRespuestas
                        ->where('cf_id', $campoDestino)
                        ->first();

                    if ($match) {
                        $valorClave = $match->valor;
                        break;
                    }
                }

                if ($valorClave && $formOrigenId) {

                    $coincide = $this->RespuestasCampoRepository->GetRespCampoByIdValor($campoOrigen, $valorClave);

                    if ($coincide) {

                        $respuestaCompleta = RespuestasForm::with('camposRespuestas')
                            ->find($coincide->respuesta_id);

                        if ($respuestaCompleta) {

                            foreach ($formulariosFinales as $formularioTarget) {

                                $esMultiple = $formularioTarget->config['registro_multiple'] ?? false;

                                if ($esMultiple)
                                    continue;

                                if ($formularioTarget->id != $formOrigenId)
                                    continue;

                                foreach ($formularioTarget->campos as $campo) {

                                    $valoresGlobal[$formularioTarget->id][$campo->id] =
                                        $respuestaCompleta->camposRespuestas
                                            ->where('cf_id', $campo->id)
                                            ->pluck('valor')
                                            ->toArray();
                                }
                            }
                        }
                    }
                }
            }
        }

        return view('formularios.editar_datos_form', [
            'formularios' => $formulariosFinales,
            'formulario' => $formulariosFinales->first(),
            'valoresGlobal' => $valoresGlobal,
            'respuesta' => $respuesta,
            'humanRules' => $humanRules,
            'breadcrumb' => $breadcrumb,
            'moduloModelo' => $moduloModelo,
            'modulo' => $modulo,
            'formulas' => $formulas,
            'edit' => 1
        ]);
    }


    public function update(Request $request, RespuestasForm $respuesta, $modulo)
    {
        $evento = 'on_update';
        DB::beginTransaction();
        try {

            $form = $respuesta->form_id;
            $formularioModelo = $this->FormularioRepository->GetFormById($form);
            $moduloModelo = $modulo > 0 ? $this->ModuloRepository->GetModuloById($modulo) : null;

            $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

            $formularios = $this->FormularioRepository->obtenerFormularios($form, $moduloModelo);
            $respuestasActualizadas = [];
            $grupo = $respuesta->grupos()->with('respuestas.camposRespuestas')->first();

            // INICIO ACTUALIZAR MÚLTIPLES
            $registrosRaw = json_decode($request->registros_json, true) ?? [];
            $registros = $this->RespuestasFormInterface->normalizarRegistros($registrosRaw);

            if ($grupo && !empty($registros)) {

                $respuestasMultiples = $grupo->respuestas
                    ->groupBy('form_id');

                foreach ($registros as $index => $registro) {

                    $formulariosEnRegistro = collect($registro)
                        ->keys()
                        ->map(fn($k) => explode('[', $k)[0])
                        ->unique();

                    foreach ($formulariosEnRegistro as $formPrefix) {

                        if (!is_string($formPrefix)) {
                            continue;
                        }

                        $formId = (int) str_replace('form_', '', $formPrefix);

                        $formularioModelo = $formularios->firstWhere('id', $formId);
                        if (!$formularioModelo)
                            continue;

                        $multiple = $formularioModelo->config['registro_multiple'] ?? false;
                        if (!$multiple)
                            continue;

                        $campos = $this->CamposFormRepository->GetCamposByForm($formId);

                        $rules = $this->RespuestasFormInterface->validacion($formularioModelo, $campos, null, 'update', $formPrefix);

                        $resultado = $this->FormularioRepository->GetData($request, $formPrefix, $rules, $registro);

                        $datosFormulario = $resultado['datosFormulario'];
                        $validator = $resultado['validator'];

                        if ($validator->fails()) {
                            DB::rollBack();
                            return back()->withErrors($validator)->withInput();
                        }

                        $errores = $this->CatalogoRepository->validarOpcionesCatalogo($campos, $datosFormulario, $formPrefix);

                        if (!empty($errores)) {
                            DB::rollBack();
                            return back()->withErrors($errores)->withInput();
                        }


                        //obtener respuesta por índice

                        $respuestaTarget = $respuestasMultiples[$formId][$index] ?? null;


                        if (!$respuestaTarget)
                            continue;

                        $filasOriginales = $this->RespuestasCampoRepository->filaDesdeRespuesta($respuestaTarget, $campos);

                        foreach ($campos as $campo) {

                            $this->CamposFormRepository->actualizarCampo($campo, $respuestaTarget->id, $datosFormulario, $formId, $formPrefix);
                        }


                        $filas = $this->RespuestasCampoRepository->filaDesdeArray($respuestaTarget, $datosFormulario, $campos);
                        $errores = array_filter($this->FormLogicInterface->ValidarLogica($respuestaTarget, $filas, $evento), fn($msg) => !empty(trim($msg)));


                        if (!empty($errores)) {
                            DB::rollBack();
                            throw new \Exception(implode('<br>', $errores));
                        }


                        $respuestasActualizadas[] = [
                            'respuesta_id' => $respuestaTarget->id,
                            'filas' => $filas,
                            'filas_originales' => $filasOriginales,
                            'form_id' => $formId
                        ];
                    }
                }

            }

            // FIN ACTUALIZAR MÚLTIPLES

            // ACTUALIZAR  (RELACION 1:N)
            $formulas = [];

            if ($moduloModelo) {
                $grupoData = $this->FormularioRepository->obtenerFormulariosDelGrupo($form, $moduloModelo->id);
                $formulas = $grupoData['grupo']->config ?? [];
            }

            foreach ($formularios as $formularioModelo) {

                $formId = $formularioModelo->id;
                $multiple = $formularioModelo->config['registro_multiple'] ?? false;

                if (!$multiple) {

                    $formPrefix = "form_{$formId}";

                    if (!$request->has($formPrefix))
                        continue;

                    // obtener fórmula relación
                    $formulaRelacion = collect($formulas)->firstWhere('relacion_multiple', 1);


                    $campoOrigen = null;

                    if ($formulaRelacion) {
                        $campoOrigen = $formulaRelacion['formula'][1]['campo_id'];
                    }

                    // CASO SIN RELACIÓN 


                    if (!$campoOrigen) {
                        $result = $this->RespuestasFormInterface->LogicaActualizacion($formId, $formPrefix, $respuesta, $formularioModelo, $request, $evento);

                        if ($result['error'] == 1) {

                            DB::rollBack();

                            return back()->withErrors($result['content'])->withInput();
                        }

                        $respuestasActualizadas[] = $result['content'];

                        continue;
                    }


                    // CASO CON RELACIÓN 

                    $campoDestinoModel = $this->CamposFormRepository->GetCampo($campoOrigen);

                    if (!$campoDestinoModel)
                        continue;

                    $valorClave = $request->input(
                        "form_{$campoDestinoModel->form_id}.{$campoDestinoModel->id}"
                    );

                    if (!$valorClave)
                        continue;

                    $coincide = $this->RespuestasCampoRepository->GetRespCampoByIdValor($campoOrigen, $valorClave);


                    if (!$coincide)
                        continue;

                    $respuestaTarget = RespuestasForm::with('camposRespuestas')->find($coincide->respuesta_id);

                    if (!$respuestaTarget)
                        continue;

                    $result = $this->RespuestasFormInterface->LogicaActualizacion($formId, $formPrefix, $respuestaTarget, $formularioModelo, $request, $evento);


                    if ($result['error'] == 1) {

                        DB::rollBack();

                        return back()->withErrors($result['content'])->withInput();
                    }

                    $respuestasActualizadas[] = $result['content'];
                }


            }


            $agrupadas = collect($respuestasActualizadas)->unique('respuesta_id')->groupBy('form_id');

            $this->FormLogicInterface->EjecutarAcciones($agrupadas, $evento);


            DB::commit();
            DB::disconnect();

            // REDIRECCIÓN
            if ($moduloModelo) {
                return redirect()->route('modulo.index', $moduloModelo->id)
                    ->with([
                        'status' => configForm($form, 'messages.success_update'),
                        'formulario_id' => $formularioModelo->id
                    ]);
            }

            return redirect()
                ->route('formularios.respuestas.formulario', $form)
                ->with('status', configForm($form, 'messages.success_update'));

        } catch (\Exception $e) {

            DB::rollBack();
            DB::disconnect();

            return back()->withErrors($e->getMessage());
        }
    }
    public function destroy(RespuestasForm $respuesta)
    {
        DB::beginTransaction();

        try {

            $resultado = $this->FormLogicInterface->LogicaEliminarRespuesta($respuesta);

            if (!$resultado['success']) {
                DB::rollBack();
                return redirect()->back()->with('error', implode('<br>', $resultado['errores']));
            }
            DB::commit();

            return redirect()->back()->with('status', $resultado['mensaje']);

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()->back()->with('error', 'Error al eliminar la respuesta.');
        }
    }

    public function EliminarRegistro(Request $request, $formId)
    {
        $formKey = 'form_' . $formId;

        $formData = $request->input($formKey);


        $query = DB::table('respuestas_campos')
            ->select('respuesta_id');

        foreach ($formData as $cf_id => $valor) {

            $query->orWhere(function ($q) use ($cf_id, $valor) {
                $q->where('cf_id', $cf_id)
                    ->where('valor', $valor);
            });

        }

        $respuesta_id = $query
            ->groupBy('respuesta_id')
            ->havingRaw('COUNT(DISTINCT cf_id) = ?', [count($formData)])
            ->pluck('respuesta_id')
            ->first();

        if ($respuesta_id != null) {
            $respuesta = RespuestasForm::find($respuesta_id);

            $evento = 'on_delete_group';

            $campos = $this->CamposFormRepository->GetCamposByForm($respuesta->form_id);

            $filas = $this->RespuestasCampoRepository->filaDesdeRespuesta($respuesta, $campos);

            $agrupadas = collect([
                $respuesta->form_id => collect([
                    [
                        'respuesta_id' => $respuesta->id,
                        'filas' => $filas,
                        'filas_originales' => []
                    ]
                ])
            ]);

            $this->FormLogicInterface->EjecutarAcciones($agrupadas, $evento);

            return response()->json([
                'success' => true,
                'message' => configForm($formId, 'messages.success_delete')
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'El registro no puede ser eliminado porque sus datos fueron modificados.'
            ]);
        }



    }

    public function eliminarMasivo(Request $request)
    {
        $ids = explode(',', $request->respuestas_ids);

        $respuestas = RespuestasForm::whereIn('id', $ids)->get();

        foreach ($respuestas as $respuesta) {
            // Eliminar archivos asociados
            $this->FormularioRepository->EliminarArchivos($respuesta);

            // Eliminar campos y respuesta
            $respuesta->camposRespuestas()->delete();
            $respuesta->delete();
        }

        return redirect()->back()->with('status', count($respuestas) . ' respuesta(s) eliminadas correctamente.');
    }

    public function CargaMasiva($form)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $form)],
            ['name' => 'Registrar Datos ', 'url' => route('permissions.index')],
        ];

        return view('formularios.carga_masiva', compact('breadcrumb', 'form'));
    }

    public function validarRegistro(Request $request)
    {
        $data = $request->all();

        $errores = [];

        foreach ($data as $formKey => $formData) {

            // Detectar solo claves tipo form_X
            if (!str_starts_with($formKey, 'form_')) {
                continue;
            }

            $formId = str_replace('form_', '', $formKey);

            $formulario = $this->FormularioRepository->GetFormById($formId);
            $campos = $this->CamposFormRepository->GetCamposByForm($formId);

            $rules = $this->RespuestasFormInterface->validacion(
                $formulario,
                $campos,
                null,
                'store',
                $formKey
            );

            $validator = validator($data, $rules);

            // nombres personalizados
            $atributos = [];

            foreach ($campos as $campo) {

                $fieldName = $formKey
                    ? "{$formKey}.{$campo->id}"
                    : $campo->id;

                $atributos[$fieldName] = $campo->etiqueta ?? $campo->id;
            }

            $validator->setAttributeNames($atributos);


            if ($validator->fails()) {
                $errores = array_merge($errores, $validator->errors()->toArray());
            }

            $erroresCatalogo = $this->CatalogoRepository
                ->validarOpcionesCatalogo($campos, $request, $formKey);

            if (!empty($erroresCatalogo)) {
                $errores = array_merge($errores, $erroresCatalogo);
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'errors' => $errores
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registro agregado correctamente.'
        ]);
    }


    /**
     * Carga masiva desde un archivo .txt separado por comas.
     */


    public function subirArchivo(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:txt,csv',
        ]);

        $archivo = $request->file('archivo');

        try {

            $resultado = $this->RespuestasFormInterface->ProcesarArchivo($archivo);

            if ($resultado['error'] == 1) {
                return response()->json(['error' => $resultado['message']], 400);
            }

            $lineasTotales = $resultado['total_lineas'];

            if ($lineasTotales == 0) {
                return response()->json(['error' => 'El archivo está vacío.'], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo guardar el archivo: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Archivo subido correctamente',
            'total' => $lineasTotales
        ]);
    }

    public function procesarChunk(Request $request)
    {
        $form = $request->form_id;

        $resultado = $this->RespuestasFormInterface->procesarChunk($form);

        if ($resultado['error'] == 1) {
            return response()->json(['error' => $resultado['message']], $resultado['codigo']);
        }

        return response()->json([
            'success' => true,
            'progreso' => $resultado['progreso'],
            'finalizado' => $resultado['finalizado'],
            'errores' => $resultado['errores'],
        ]);
    }

    public function descargarPlantilla($form)
    {
        $campos = $this->CamposFormRepository->GetCamposByForm($form);

        if ($campos->isEmpty()) {
            return back()->withErrors('No hay campos definidos para este formulario.');
        }

        $generador = $this->RespuestasFormInterface->GeneraPlantilla($campos, $form);

        $nombreArchivo = $generador['nombreArchivo'];

        return response($generador['contenido'])
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename={$nombreArchivo}");
    }



}
