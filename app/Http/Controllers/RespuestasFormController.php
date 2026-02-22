<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use App\Models\CamposForm;
use App\Models\Formulario;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogo;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\FormLogicInterface;
use App\Interfaces\RespuestasFormInterface;
use Illuminate\Support\Facades\Validator;

use App\Models\FormLogicCondition;


use Jenssegers\Agent\Agent;

use App\Jobs\EjecutarLogicaFormulario;
use App\Models\FormLogicRule;
use App\Models\Modulo;

class RespuestasFormController extends Controller
{


    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $FormLogicInterface;
    protected $RespuestasFormInterface;


    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        FormLogicInterface $formLogicInterface,
        RespuestasFormInterface $respuestasFormInterface
    ) {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->FormLogicInterface = $formLogicInterface;
        $this->RespuestasFormInterface = $respuestasFormInterface;


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

        $agent = new Agent();
        $isMobile = $agent->isMobile();

        $resultado = $this->FormularioRepository->procesarFormularioConFiltros(Formulario::with('campos')->findOrFail($form), $request);

        $campos = $resultado['formulario']->campos;






        DB::disconnect();
        return view('formularios.respuestas_formulario', array_merge($resultado, compact('isMobile', 'breadcrumb', 'campos')));
    }




    public function create($form, $modulo)
    {

        // Buscar los modelos
        $formularioModelo = Formulario::find($form);
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

        // Construir breadcrumb
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

        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])->findOrFail($formularioModelo->id);


        // Procesar los campos para agregar opciones de catálogo o de formulario referenciado
        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);
        // Asignar los campos procesados al formulario


        $formulario->campos = $camposProcesados;
        // inicio
        $rules = collect();
        $campos = $formulario->campos;


        foreach ($campos as $campo) {
            $reglasCampo = FormLogicCondition::with([
                'campoCondicion.formulario',
                'campoValor.formulario',
                'action.campoDestino.formulario'
            ])->where('campo_condicion', $campo->id)->get();

            $rules = $rules->merge($reglasCampo);
        }

        $humanRules = $this->RespuestasFormInterface->GetHumanRules($rules);
        // fin

        return view('formularios.registrar_datos_form', compact(
            'humanRules',
            'formulario',
            'breadcrumb',
            'moduloModelo',
            'modulo'
        ));
    }
    /*
        public function store(Request $request, $form, $modulo, $tipo)
        {

            // Buscar los modelos
            $formularioModelo = Formulario::find($form);
            $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

            $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);


            $campos = CamposForm::where('form_id', $form)->get();

            $rules = $this->RespuestasFormInterface->validacion($campos);

            $request->validate($rules);

            $errores = $this->FormularioRepository->validarOpcionesCatalogo($campos, $request);

            if (!empty($errores)) {
                return redirect()->back()->withErrors($errores)->withInput();
            }


            DB::beginTransaction();
            try {
                $respuesta = $this->FormularioRepository->crearRespuesta($form);



                foreach ($campos as $campo) {
                    $this->FormularioRepository->guardarCampo($campo, $respuesta->id, $request, $form);
                }


                $filasSeleccionadas = $this->RespuestasFormInterface->fila($request);

                $evento = 'on_create';

                $resultado = $this->FormLogicInterface->ValidarLogica($respuesta, $filasSeleccionadas, $evento);

                //Eliminar valores vacíos o nulos
                $resultado = array_filter($resultado, fn($msg) => !empty(trim($msg)));

                //Si hay mensajes de error, cancelar la transacción y retornar
                if (!empty($resultado)) {

                    DB::rollBack();

                    $mensaje = implode('<br>', $resultado);

                    return back()
                        ->withErrors(['logica' => $mensaje])
                        ->withInput();

                } else {
                    $usuario = auth()->id();

                    EjecutarLogicaFormulario::dispatch(
                        $respuesta,
                        $filasSeleccionadas,
                        $evento,
                        $usuario,
                        env('APP_URL')
                    );
                }


                DB::commit();

                DB::disconnect();


                //Definir retorno de ruta 
                if ($tipo == 0) {
                    if ($moduloModelo) {

                        return redirect()->route('modulo.index', $moduloModelo->id)
                            ->with([
                                'status' => 'Registro creado correctamente.',
                                'formulario_id' => $formularioModelo->id
                            ]);
                    } else {

                        return redirect()->route('formularios.respuestas.formulario', $form)
                            ->with('status', 'Registro creado correctamente.');
                    }
                } else {
                    return redirect()->route('home')
                        ->with('status', 'Registro creado correctamente.');
                }



            } catch (\Exception $e) {
                DB::rollBack();
                DB::disconnect();
                return redirect()->back()->withErrors('Error al guardar el formulario: ' . $e->getMessage());
            }

        }
    */



    public function store(Request $request, $form, $modulo, $tipo)
    {

        //dd($request);
        $formularioModelo = Formulario::find($form);
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->RespuestasFormInterface
            ->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);

        $campos = CamposForm::where('form_id', $form)->get();
        $rules = $this->RespuestasFormInterface->validacion($formularioModelo, $campos);

        // ============================================
        // SI NO ES REGISTRO MULTIPLE 
        // ============================================
        if (!($formularioModelo->config['registro_multiple'] ?? false)) {

            $request->validate($rules);

            $errores = $this->FormularioRepository
                ->validarOpcionesCatalogo($campos, $request);

            if (!empty($errores)) {
                return redirect()->back()->withErrors($errores)->withInput();
            }

            DB::beginTransaction();

            try {

                $respuesta = $this->FormularioRepository
                    ->crearRespuesta($form);

                foreach ($campos as $campo) {
                    $this->FormularioRepository
                        ->guardarCampo($campo, $respuesta->id, $request, $form);
                }

                $filasSeleccionadas = $this->RespuestasFormInterface
                    ->fila($request);

                $this->procesarLogica($respuesta, $filasSeleccionadas);


                DB::commit();
                DB::disconnect();

            } catch (\Exception $e) {

                DB::rollBack();
                DB::disconnect();

                return back()
                    ->withErrors('Error al guardar el formulario: ' . $e->getMessage());
            }

        }

        // ============================================
        // REGISTRO MÚLTIPLE
        // ============================================
        else {

            $registros = json_decode($request->registros_json, true);

            if (empty($registros)) {
                return back()->withErrors([
                    'registros_json' => 'Debe agregar al menos un registro.'
                ])->withInput();
            }

            DB::beginTransaction();

            try {

                foreach ($registros as $index => $registroData) {

                    // Validar cada registro

                    $validator = Validator::make($registroData, $rules);
                    if ($validator->fails()) {
                        DB::rollBack();
                        return back()
                            ->withErrors($validator)
                            ->withInput();
                    }

                    $respuesta = $this->FormularioRepository
                        ->crearRespuesta($form);

                    foreach ($campos as $campo) {

                        $tipo_ = strtolower($campo->campo_nombre);
                        $nombreCampo = $campo->nombre;

                        if (in_array($tipo_, ['imagen', 'video', 'archivo'])) {

                            if ($formularioModelo->config['registro_multiple']) {

                                $archivos = $request->file("registros.$index.$nombreCampo");

                                if ($archivos) {

                                    $requestIndividual = new \Illuminate\Http\Request();
                                    $requestIndividual->files->set($nombreCampo, $archivos);

                                    $this->FormularioRepository->guardarCampo($campo, $respuesta->id, $requestIndividual, $formularioModelo->id);
                                }

                            } else {

                                $this->FormularioRepository->guardarCampo($campo, $respuesta->id, $request, $formularioModelo->id);
                            }

                        } else {

                            $valor = $registroData[$nombreCampo] ?? null;

                            if ($valor !== null) {

                                $this->FormularioRepository->guardarValorSimple($campo, $respuesta->id, $valor);

                            }
                        }
                    }

                    $filasSeleccionadas = $this->RespuestasFormInterface
                        ->filaDesdeArray($registroData);


                    $this->procesarLogica($respuesta, $filasSeleccionadas);

                }

                DB::commit();
                DB::disconnect();

            } catch (\Exception $e) {

                DB::rollBack();
                DB::disconnect();

                return back()
                    ->withErrors('Error en registros múltiples: ' . $e->getMessage());
            }
        }



        if ($tipo == 0) {

            if ($moduloModelo) {

                return redirect()->route('modulo.index', $moduloModelo->id)
                    ->with([
                        'status' => 'Registro(s) creado(s) correctamente.',
                        'formulario_id' => $formularioModelo->id
                    ]);

            } else {

                return redirect()
                    ->route('formularios.respuestas.formulario', $form)
                    ->with('status', 'Registro(s) creado(s) correctamente.');
            }

        } else {

            return redirect()
                ->route('home')
                ->with('status', 'Registro(s) creado(s) correctamente.');
        }
    }



    private function procesarLogica($respuesta, $filasSeleccionadas)
    {
        $evento = 'on_create';

        $resultado = $this->FormLogicInterface
            ->ValidarLogica($respuesta, $filasSeleccionadas, $evento);

        $resultado = array_filter(
            $resultado,
            fn($msg) => !empty(trim($msg))
        );

        if (!empty($resultado)) {

            DB::rollBack();

            throw new \Exception(implode('<br>', $resultado));
        }

        EjecutarLogicaFormulario::dispatch(
            $respuesta,
            $filasSeleccionadas,
            $evento,
            auth()->id(),
            env('APP_URL')
        );
    }

    public function validarRegistro(Request $request, $form)
    {
        $campos = CamposForm::where('form_id', $form)->get();
        $formulario = Formulario::find($form);
        $rules = $this->RespuestasFormInterface->validacion($formulario, $campos);

        $validated = validator($request->all(), $rules);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors()
            ]);
        }

        $erroresCatalogo = $this->FormularioRepository
            ->validarOpcionesCatalogo($campos, $request);

        if (!empty($erroresCatalogo)) {
            return response()->json([
                'success' => false,
                'errors' => $erroresCatalogo
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

        if (!$archivo->isValid()) {
            return response()->json(['error' => 'Archivo no válido.'], 400);
        }

        $nombre = time() . '_' . $archivo->getClientOriginalName();
        $destino = storage_path('app/import_temp');

        // Asegurarse que la carpeta exista
        if (!file_exists($destino)) {
            mkdir($destino, 0777, true);
        }

        try {
            // Mover el archivo al destino
            $archivo->move($destino, $nombre);
            $path = "import_temp/$nombre";

            // Contar las líneas totales
            $lineasTotales = count(file(storage_path("app/$path")));

            // Guardamos info en sesión
            Session::put('import_file_path', $path);
            Session::put('import_total_lines', $lineasTotales);
            Session::put('import_last_line', 1); // empezamos después de la cabecera

            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'total' => $lineasTotales
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo guardar el archivo: ' . $e->getMessage()], 500);
        }
    }

    public function procesarChunk(Request $request)
    {
        $form = $request->form_id;
        $chunkSize = 1000;

        $path = Session::get('import_file_path');
        $lastLine = Session::get('import_last_line', 1);

        if (!$path) {
            return response()->json(['error' => 'No hay archivo cargado.'], 400);
        }

        $handle = fopen(storage_path("app/$path"), 'r');
        if (!$handle) {
            return response()->json(['error' => 'No se pudo abrir el archivo.'], 500);
        }

        $campos = CamposForm::where('form_id', $form)->get();
        if ($campos->isEmpty()) {
            fclose($handle);
            return response()->json(['error' => "No se encontraron campos para este formulario."], 400);
        }

        $contador = 0;
        $errores = [];

        // Saltamos hasta la última línea procesada
        for ($i = 0; $i < $lastLine; $i++) {
            fgetcsv($handle);
        }

        DB::beginTransaction();
        try {
            while (($linea = fgetcsv($handle, 0, ',')) !== false && $contador < $chunkSize) {
                $contador++;
                $lastLine++;

                if (count($linea) !== count($campos)) {
                    $errores[] = "Línea {$lastLine}: columnas incorrectas.";
                    continue;
                }

                $dataAsociativa = [];
                foreach ($campos as $index => $campo) {
                    $dataAsociativa[$campo->nombre] = $linea[$index] ?? null;
                }

                // Crear respuesta usando tu repositorio
                $respuesta = $this->FormularioRepository->crearRespuesta($form);

                foreach ($campos as $campo) {
                    $valor = $dataAsociativa[$campo->nombre] ?? null;
                    if ($valor === null)
                        continue;

                    $tipo = strtolower($campo->campo_nombre);

                    if (in_array($tipo, ['imagen', 'video', 'archivo'])) {
                        $this->FormularioRepository->guardarArchivoGenerico($campo, $respuesta->id, $form, $valor);
                    } else {
                        $this->FormularioRepository->guardarValorSimple($campo, $respuesta->id, $valor);
                    }
                }
            }

            DB::commit();
            DB::disconnect();
        } catch (\Exception $e) {
            DB::rollBack();
            DB::disconnect();
            fclose($handle);
            return response()->json(['error' => $e->getMessage()], 500);
        } finally {
            fclose($handle);
        }

        Session::put('import_last_line', $lastLine);

        $total = Session::get('import_total_lines');
        $progreso = min(100, round(($lastLine / $total) * 100));

        $finalizado = $lastLine >= $total;

        return response()->json([
            'success' => true,
            'progreso' => $progreso,
            'finalizado' => $finalizado,
            'errores' => $errores,
        ]);
    }


    public function descargarPlantilla($form)
    {
        $campos = CamposForm::where('form_id', $form)->get();

        if ($campos->isEmpty()) {
            return back()->withErrors('No hay campos definidos para este formulario.');
        }

        $generador = $this->RespuestasFormInterface->GeneraPlantilla($campos, $form);

        $nombreArchivo = $generador['nombreArchivo'];

        return response($generador['contenido'])
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename={$nombreArchivo}");
    }




    public function edit(RespuestasForm $respuesta, $modulo)
    {



        $formulario = $respuesta->formulario()->with('campos')->first();

        // Buscar los modelos
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->RespuestasFormInterface->validacion_modulo_form($formulario, $moduloModelo, $modulo);


        // Construir breadcrumb
        if ($moduloModelo) {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Módulo ' . $moduloModelo->nombre, 'url' => route('modulo.index', $moduloModelo->id)],
                ['name' => 'Editar ' . $formulario->nombre, 'url' => route('permissions.index')],
            ];
        } else {
            $breadcrumb = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Formularios', 'url' => route('formularios.index')],
                ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $formulario)],
                ['name' => 'Editar Datos ', 'url' => route('permissions.index')],
            ];
        }


        // Cargar las opciones de catálogo para cada campo
        $campos = $formulario->campos->sortBy('posicion')->map(function ($campo) {
            $campo->opciones_catalogo = $campo->categoria_id
                ? Catalogo::where('categoria_id', $campo->categoria_id)->get()
                : collect([]);
            return $campo;
        });

        // Procesar los campos para agregar opciones de catálogo o de formulario referenciado
        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);
        // Asignar los campos procesados al formulario
        $formulario->campos = $camposProcesados;

        return view('formularios.editar_datos_form', compact(
            'breadcrumb',
            'respuesta',
            'formulario',
            'campos',
            'moduloModelo',
            'modulo'
        ));
    }

    public function update(Request $request, RespuestasForm $respuesta, $modulo)
    {
        $form = $respuesta->form_id;


        // Buscar los modelos
        $formularioModelo = Formulario::find($form);
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->RespuestasFormInterface->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);


        // 1️ Obtener los campos del formulario
        $campos = CamposForm::where('form_id', $form)->get();

        // 2️ Construir reglas dinámicas

        $rules = $this->RespuestasFormInterface->validacion($campos, $respuesta->id);
        // 3️ Validar los datos

        $validatedData = $request->validate($rules);

        // 4️ Validar opciones de catálogo
        $errores = $this->FormularioRepository->validarOpcionesCatalogo($campos, $request);

        if (!empty($errores)) {
            return redirect()->back()->withErrors($errores)->withInput();
        }

        // 5️ Guardar dentro de transacción
        DB::beginTransaction();
        try {

            foreach ($campos as $campo) {
                $name = $campo->nombre;
                $tipo = strtolower($campo->campo_nombre);

                // Obtener valor existente
                $old = RespuestasCampo::where('respuesta_id', $respuesta->id)
                    ->where('cf_id', $campo->id)
                    ->first();

                $valor = null;

                if (in_array($tipo, ['imagen', 'video', 'archivo'])) {
                    if ($request->hasFile($name)) {
                        // Subir nuevo archivo y eliminar el anterior
                        if ($old && $old->valor) {
                            $oldPath = match ($tipo) {
                                'imagen' => public_path("archivos/formulario_{$form}/imagenes/{$old->valor}"),
                                'video' => public_path("archivos/formulario_{$form}/videos/{$old->valor}"),
                                'archivo' => public_path("archivos/formulario_{$form}/archivos/{$old->valor}"),
                            };
                            if (file_exists($oldPath))
                                unlink($oldPath);
                        }

                        $file = $request->file($name);
                        $filename = uniqid($tipo . '_') . '.' . $file->getClientOriginalExtension();
                        $path = match ($tipo) {
                            'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                            'video' => public_path("archivos/formulario_{$form}/videos"),
                            'archivo' => public_path("archivos/formulario_{$form}/archivos"),
                        };
                        if (!file_exists($path))
                            mkdir($path, 0777, true);
                        $file->move($path, $filename);

                        $valor = $filename;
                    } elseif ($old) {
                        // Mantener valor anterior si no hay archivo nuevo
                        $valor = $old->valor;
                    }
                } else {
                    if ($request->has($name)) {
                        $valor = $request->input($name);
                    }
                }

                // Guardar o actualizar

                if ($valor !== null) {
                    // Convertir todo a array para unificar lógica
                    $valoresNuevos = is_array($valor) ? $valor : [$valor];

                    // Obtener todos los valores antiguos de este campo
                    $valoresAntiguos = RespuestasCampo::where('respuesta_id', $respuesta->id)
                        ->where('cf_id', $campo->id)
                        ->pluck('valor')
                        ->toArray();

                    // 1️ Agregar valores que no existían
                    foreach ($valoresNuevos as $v) {
                        if (!in_array($v, $valoresAntiguos)) {
                            RespuestasCampo::create([
                                'respuesta_id' => $respuesta->id,
                                'cf_id' => $campo->id,
                                'valor' => $v,
                            ]);
                        }
                    }

                    // 2️ Eliminar los valores que ya no están seleccionados
                    $valoresAEliminar = array_diff($valoresAntiguos, $valoresNuevos);
                    if (!empty($valoresAEliminar)) {
                        RespuestasCampo::where('respuesta_id', $respuesta->id)
                            ->where('cf_id', $campo->id)
                            ->whereIn('valor', $valoresAEliminar)
                            ->delete();
                    }
                }
            }


            DB::commit();
            DB::disconnect();


            //Definir retorno de ruta 
            if ($moduloModelo) {

                return redirect()->route('modulo.index', $moduloModelo->id)
                    ->with([
                        'status' => 'Respuesta actualizada correctamente.',
                        'formulario_id' => $formularioModelo->id
                    ]);
            } else {

                return redirect()->route('formularios.respuestas.formulario', $form)
                    ->with('status', 'Respuesta actualizada correctamente.');
            }



        } catch (\Exception $e) {
            DB::rollBack();
            DB::disconnect();
            return redirect()->back()->withErrors('Error al actualizar la respuesta: ' . $e->getMessage());
        }
    }





    public function destroy(RespuestasForm $respuesta)
    {

        $this->RespuestasFormInterface->EliminarArchivos($respuesta);

        // Borrar registros de campos y respuesta principal
        $respuesta->camposRespuestas()->delete();
        $respuesta->delete();

        return redirect()->back()->with('status', 'Respuesta eliminada correctamente.');
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
}
