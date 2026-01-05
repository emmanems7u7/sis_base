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

    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioInterface,
        FormLogicInterface $formLogicInterface
    ) {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->FormLogicInterface = $formLogicInterface;


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

    function validacion_modulo_form($formularioModelo, $moduloModelo, $modulo)
    {

        // Validaciones
        if ($moduloModelo) {
            // Caso: venimos desde módulo → ambos deben existir
            if (!$formularioModelo) {
                return redirect()->back()->with('error', 'Formulario no encontrado para este módulo.');
            }
        } else {
            // Caso: venimos desde formularios → solo se requiere formulario
            if (!$formularioModelo) {
                return redirect()->back()->with('error', 'Formulario no encontrado.');
            }
        }

        // Validación extra: módulo >0 pero no encontrado
        if ($modulo > 0 && $moduloModelo === null) {
            return redirect()->back()->with('error', 'Módulo no encontrado.');
        }
    }


    public function create($form, $modulo)
    {

        // Buscar los modelos
        $formularioModelo = Formulario::find($form);
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);


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

        $humanRules = [];

        foreach ($rules as $condicion) {
            $campoCond = $condicion->campoCondicion;
            $campoVal = $condicion->campoValor;

            $formOrigen = $campoCond ? $campoCond->formulario->nombre ?? 'Formulario desconocido' : 'Campo desconocido';
            $formValor = $campoVal ? $campoVal->formulario->nombre ?? 'Formulario desconocido' : null;

            $valorTexto = $campoVal
                ? "<strong>{$campoVal->etiqueta}</strong> del formulario <em>'{$formValor}'</em>"
                : "<strong>{$condicion->valor}</strong>";

            // Convertir operadores a texto entendible
            $operadorTexto = match ($condicion->operador) {
                '=' => '<strong> es igual a</strong>',
                '!=' => '<strong> es distinto de</strong>',
                '>' => '<strong> es mayor que</strong>',
                '<' => '<strong> es menor que</strong>',
                '>=' => '<strong> es mayor o igual que</strong>',
                '<=' => '<strong> es menor o igual que</strong>',
                'in' => '<strong> es contenido en</strong>',
                default => "<strong>{$condicion->operador}</strong>"
            };

            $accionTexto = '<em>Sin acción definida</em>';
            if ($condicion->action && $condicion->action->campoDestino) {
                $campoAccion = $condicion->action->campoDestino;
                $formAccion = $campoAccion->formulario ?? null;
                $accionTexto = $formAccion
                    ? "Aplicar acción <strong>'{$condicion->action->operacion}'</strong> al campo <strong>'{$campoAccion->etiqueta}'</strong> del formulario <em>'{$formAccion->nombre}'</em>"
                    : "Aplicar acción <strong>'{$condicion->action->operacion}'</strong> al campo <strong>'{$campoAccion->etiqueta}'</strong>";
            }

            // Icono de contexto al inicio de la regla
            $humanRules[] = "
                <div class='mb-2'>
                    <i class='fas fa-clipboard-list me-1'></i>
                    Si el campo <strong>'{$campoCond->etiqueta}'</strong> del formulario <em>'{$formOrigen}'</em>  
                     {$operadorTexto} {$valorTexto},<br>
                    entonces {$accionTexto} <strong> caso contrario no proceder con el registro hasta cumplir con la regla.  </strong>
                </div>
            ";
        }
        // fin

        return view('formularios.registrar_datos_form', compact(
            'humanRules',
            'formulario',
            'breadcrumb',
            'moduloModelo',
            'modulo'
        ));
    }



    function fila($request)
    {
        // Obtener todos los datos enviados
        $datosFormulario = $request->all();

        // Array para guardar filas completas seleccionadas
        $filasSeleccionadas = [];

        // Iterar sobre cada campo enviado
        foreach ($datosFormulario as $nombreCampo => $valor) {


            // Verificar si este campo es de tipo referencia a otro formulario
            // Por ejemplo: si $valor es numérico y corresponde a un ID de RespuestasForm
            if (is_numeric($valor)) {
                $fila = RespuestasForm::with('camposRespuestas.campo')
                    ->find($valor);



                if ($fila) {

                    $datos = [];
                    foreach ($fila->camposRespuestas as $cr) {
                        $datos[$cr->campo->nombre] = $cr->valor . ' - ' . $cr->id;
                    }

                    $filasSeleccionadas[$nombreCampo] = $datos;
                }
            }

            // Si es checkbox múltiple
            if (is_array($valor)) {
                foreach ($valor as $id) {
                    if (is_numeric($id)) {
                        $fila = RespuestasForm::with('camposRespuestas.campo')
                            ->find($id);

                        if ($fila) {
                            $datos = [];
                            foreach ($fila->camposRespuestas as $cr) {
                                $datos[$cr->campo->nombre] = $cr->valor . ' - ' . $cr->id;
                            }

                            $filasSeleccionadas[$nombreCampo][] = $datos;

                        }
                    }
                }
            }
        }

        return $filasSeleccionadas;
    }
    public function store(Request $request, $form, $modulo)
    {

        // Buscar los modelos
        $formularioModelo = Formulario::find($form);
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);


        $campos = CamposForm::where('form_id', $form)->get();

        $rules = $this->validacion($campos);

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


            $filasSeleccionadas = $this->fila($request);

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


        } catch (\Exception $e) {
            DB::rollBack();
            DB::disconnect();
            return redirect()->back()->withErrors('Error al guardar el formulario: ' . $e->getMessage());
        }

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

        $comentarios = "/* BORRA ESTO ANTES DE CARGAR INFORMACIÓN DE REFERENCIA */" . PHP_EOL;
        $comentarios .= "/* Explicación de los campos: */" . PHP_EOL;

        $columnas = [];
        $ejemplo = [];

        foreach ($campos as $campo) {
            $nombre = $campo->nombre;
            $tipo = strtolower($campo->campo_nombre);

            // Comentario explicativo
            $descTipo = match ($tipo) {
                'text', 'textarea' => "Texto libre",
                'number' => "Número",
                'checkbox', 'radio', 'selector' => "Selección de catálogo",
                'imagen' => "Ruta de imagen",
                'video' => "Ruta de video",
                'archivo' => "Ruta de archivo",
                'color' => "Color hexadecimal",
                'email' => "Correo electrónico",
                'password' => "Contraseña",
                'enlace' => "URL",
                'fecha' => "Fecha (YYYY-MM-DD)",
                'hora' => "Hora (HH:MM)",
                default => "Valor"
            };

            $comentarios .= "/* {$nombre}: Tipo {$tipo} -> {$descTipo} */" . PHP_EOL;

            // Nombres de columnas
            $columnas[] = $nombre;

            // Valores de ejemplo
            $valorEjemplo = match ($tipo) {
                'text', 'textarea' => 'Ejemplo de texto',
                'number' => '123',
                'checkbox' => 'opcion1|opcion2',
                'radio', 'selector' => $campo->opciones_catalogo->first()->catalogo_codigo ?? 'opcion1',
                'imagen' => 'ruta/imagen.jpg',
                'video' => 'ruta/video.mp4',
                'archivo' => 'ruta/documento.pdf',
                'color' => '#FF5733',
                'email' => 'usuario@ejemplo.com',
                'password' => 'MiClave123',
                'enlace' => 'https://ejemplo.com',
                'fecha' => now()->format('Y-m-d'),
                'hora' => now()->format('H:i'),
                default => 'valor'
            };
            $ejemplo[] = $valorEjemplo;
        }
        $comentarios .= "/* NO DEBEN EXISTIR ESPACIOS ARRIBA DEL NOMBRE DE LA COLUMNA */" . PHP_EOL;
        $comentarios .= "/* BORRA HASTA ACA ANTES DE CARGAR INFORMACIÓN DE REFERENCIA */" . PHP_EOL;

        // Crear contenido final
        $contenido = $comentarios . PHP_EOL
            . implode(',', $columnas) . PHP_EOL
            . implode(',', $ejemplo);

        $nombreArchivo = 'plantilla_formulario_' . $form . '.txt';

        return response($contenido)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename={$nombreArchivo}");
    }




    public function edit(RespuestasForm $respuesta, $modulo)
    {



        $formulario = $respuesta->formulario()->with('campos')->first();

        // Buscar los modelos
        $moduloModelo = $modulo > 0 ? Modulo::find($modulo) : null;

        $this->validacion_modulo_form($formulario, $moduloModelo, $modulo);


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

        $this->validacion_modulo_form($formularioModelo, $moduloModelo, $modulo);





        // 1️ Obtener los campos del formulario
        $campos = CamposForm::where('form_id', $form)->get();

        // 2️ Construir reglas dinámicas
        $rules = $this->validacion($campos, $respuesta->id);
        // 3️ Validar los datos
        //dd($request, $rules);
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
                    if (is_array($valor)) {
                        foreach ($valor as $v) {
                            if ($old) {
                                $old->update(['valor' => $v]);
                            } else {
                                RespuestasCampo::create([
                                    'respuesta_id' => $respuesta->id,
                                    'cf_id' => $campo->id,
                                    'valor' => $v,
                                ]);
                            }
                        }
                    } else {
                        if ($old) {
                            $old->update(['valor' => $valor]);
                        } else {
                            RespuestasCampo::create([
                                'respuesta_id' => $respuesta->id,
                                'cf_id' => $campo->id,
                                'valor' => $valor,
                            ]);
                        }
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
    public function validacion($campos, $respuestaId = null, $modo = 'store')
    {
        $rules = [];

        foreach ($campos as $campo) {
            $tipo = strtolower($campo->campo_nombre);
            $required = $campo->requerido ? 'required' : 'nullable';

            // Si es importación desde archivo, omitimos multimedia
            if ($modo === 'archivo' && in_array($tipo, ['archivo', 'imagen', 'video'])) {
                continue;
            }

            switch ($tipo) {
                case 'text':
                case 'textarea':
                    $rules[$campo->nombre] = [$required, 'string', 'max:255'];
                    break;

                case 'number':
                    $rules[$campo->nombre] = [$required, 'numeric'];
                    break;

                case 'checkbox':
                    $arrayRules = [$required, 'array'];
                    if ($campo->requerido) {
                        $arrayRules[] = 'min:1';
                    }
                    $rules[$campo->nombre] = $arrayRules;
                    break;

                case 'radio':
                case 'selector':
                    $rules[$campo->nombre] = [$required];
                    break;

                case 'archivo':
                case 'imagen':
                case 'video':
                    // Solo para store normal
                    $extensiones_permitidas = $this->CatalogoRepository
                        ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);

                    $extensiones = $extensiones_permitidas
                        ->pluck('catalogo_descripcion')
                        ->filter()
                        ->toArray();

                    $extensionesStr = !empty($extensiones) ? implode(',', $extensiones) : '';

                    $fileRules = [$required, 'file', 'max:50240']; // 50 MB aprox.

                    if (!empty($extensionesStr)) {
                        $fileRules[] = 'mimes:' . $extensionesStr;
                    }

                    $rules[$campo->nombre] = $fileRules;
                    break;

                case 'color':
                    $rules[$campo->nombre] = [$required, 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
                    break;

                case 'email':
                    $uniqueRule = $respuestaId
                        ? "unique:respuestas_campos,valor,{$respuestaId},respuesta_id,cf_id,{$campo->id}"
                        : "unique:respuestas_campos,valor,NULL,id,cf_id,{$campo->id}";
                    $rules[$campo->nombre] = [$required, 'email', 'max:255', $uniqueRule];
                    break;

                case 'password':
                    $rules[$campo->nombre] = [$required, 'string', 'min:6', 'max:255'];
                    break;

                case 'enlace':
                    $rules[$campo->nombre] = [$required, 'url'];
                    break;

                case 'fecha':
                    $rules[$campo->nombre] = [$required, 'date'];
                    break;

                case 'hora':
                    $rules[$campo->nombre] = [$required, 'date_format:H:i'];
                    break;

                default:
                    $rules[$campo->nombre] = [$required];
            }
        }

        return $rules;
    }



    public function destroy(RespuestasForm $respuesta)
    {
        // Recorrer los campos de la respuesta
        foreach ($respuesta->camposRespuestas as $campo) {
            $tipo = strtolower($campo->campo->campo_nombre ?? ''); // Asegúrate de tener la relación campo
            $valor = $campo->valor;

            if (in_array($tipo, ['imagen', 'video', 'archivo']) && $valor) {
                $path = match ($tipo) {
                    'imagen' => public_path("archivos/formulario_{$respuesta->form_id}/imagenes/{$valor}"),
                    'video' => public_path("archivos/formulario_{$respuesta->form_id}/videos/{$valor}"),
                    'archivo' => public_path("archivos/formulario_{$respuesta->form_id}/archivos/{$valor}"),
                    default => null,
                };

                if ($path && file_exists($path)) {
                    unlink($path);
                }
            }
        }

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
