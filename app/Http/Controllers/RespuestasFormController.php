<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use App\Models\CamposForm;
use App\Models\Formulario;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogo;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class RespuestasFormController extends Controller
{


    protected $CatalogoRepository;
    protected $FormularioRepository;

    public function __construct(CatalogoInterface $catalogoInterface, FormularioInterface $formularioInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;


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
            ['name' => 'Respuestas ', 'url' => route('formularios.index')],
        ];

        $agent = new Agent();
        $isMobile = $agent->isMobile();
        $formulario = Formulario::with('campos')->findOrFail($form);

        // Procesar los campos para agregar opciones de catálogo o de formulario referenciado
        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);
        // Asignar los campos procesados al formulario
        $formulario->campos = $camposProcesados;


        $query = $formulario->respuestas()->with('camposRespuestas.campo', 'actor');

        // Filtro de búsqueda por nombre del actor
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('actor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Paginación
        $respuestas = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('formularios.respuestas_formulario', compact('isMobile', 'formulario', 'respuestas', 'breadcrumb'));
    }

    public function create($form)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $form)],

            ['name' => 'Registrar Datos ', 'url' => route('permissions.index')],
        ];

        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])->findOrFail($form);


        // Procesar los campos para agregar opciones de catálogo o de formulario referenciado
        $camposProcesados = $this->FormularioRepository->CamposFormCat($formulario->campos);
        // Asignar los campos procesados al formulario
        $formulario->campos = $camposProcesados;

        return view('formularios.registrar_datos_form', compact('formulario', 'breadcrumb'));
    }




    public function store(Request $request, $form)
    {
        $campos = CamposForm::where('form_id', $form)->get();

        $rules = $this->validacion($campos);
        $validatedData = $request->validate($rules);

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

            DB::commit();


            return redirect()->route('formularios.respuestas.formulario', $form)
                ->with('status', 'Formulario enviado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Error al guardar el formulario: ' . $e->getMessage());
        }
    }


    /**
     * Carga masiva desde un archivo .txt separado por comas.
     */
    public function importarDesdeArchivo(Request $request, $form)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:txt,csv',
        ]);

        $path = $request->file('archivo')->getRealPath();
        $lineas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lineas)) {
            return back()->withErrors('El archivo está vacío.');
        }

        $campos = CamposForm::where('form_id', $form)->get();
        $nombresCampos = $campos->pluck('nombre')->toArray();


        // Validar primera línea: nombres de columnas
        $primeraLinea = str_getcsv(array_shift($lineas), ',');
        if ($primeraLinea !== $nombresCampos) {
            return back()->withErrors('La primera fila del archivo no coincide con los nombres de los campos del formulario.');
        }

        $erroresImportacion = [];
        $contadorLinea = 1; // primera fila de datos será la 2
        $respuestasTemp = [];

        // Filtrar líneas vacías
        $lineas = array_filter($lineas, fn($l) => trim($l) !== '');

        DB::beginTransaction();
        try {
            foreach ($lineas as $linea) {
                $contadorLinea++;
                $datos = str_getcsv($linea, ',');

                // Validar cantidad de columnas
                if (count($datos) !== count($campos)) {
                    $erroresImportacion[] = "Línea {$contadorLinea}: La cantidad de columnas no coincide con la del formulario.";
                    continue;
                }

                $dataAsociativa = [];
                foreach ($campos as $index => $campo) {
                    $dataAsociativa[$campo->nombre] = $datos[$index] ?? null;
                }

                $fakeRequest = new Request($dataAsociativa);

                // Validar reglas del formulario
                $rules = $this->validacion($campos, null, 'archivo');
                $validator = Validator::make($fakeRequest->all(), $rules);

                if ($validator->fails()) {
                    $errores = $validator->errors()->all();
                    foreach ($errores as $error) {
                        $erroresImportacion[] = "Línea {$contadorLinea}: {$error}";
                    }
                    continue;
                }
                // Validar catálogo
                $erroresCatalogo = $this->FormularioRepository->validarOpcionesCatalogo($campos, $fakeRequest);
                if (!empty($erroresCatalogo)) {
                    foreach ($erroresCatalogo as $error) {
                        $erroresImportacion[] = "Línea {$contadorLinea}: {$error}";
                    }
                    continue;
                }

                $respuestasTemp[] = $dataAsociativa;
            }

            // Si hay errores, no hacemos commit
            if (!empty($erroresImportacion)) {
                DB::rollBack();
                return back()
                    ->with('erroresImportacion', $erroresImportacion)
                    ->with('warning', 'La importación no se completó. Se detectaron errores en algunos registros.');
            }

            // Guardar respuestas válidas
            $totalCargados = 0;
            foreach ($respuestasTemp as $dataAsociativa) {
                $respuesta = $this->FormularioRepository->crearRespuesta($form);
                foreach ($campos as $index => $campo) {
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
                $totalCargados++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error durante la importación: ' . $e->getMessage());
        }

        return back()->with('status', "Importación masiva completada correctamente. Total de registros cargados: {$totalCargados}");
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




    public function edit(RespuestasForm $respuesta)
    {

        $formulario = $respuesta->formulario()->with('campos')->first();
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $formulario)],

            ['name' => 'Editar Datos ', 'url' => route('permissions.index')],
        ];

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

        return view('formularios.editar_datos_form', compact('breadcrumb', 'respuesta', 'formulario', 'campos'));
    }

    public function update(Request $request, RespuestasForm $respuesta)
    {
        $form = $respuesta->form_id;

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
            return redirect()->route('formularios.respuestas.formulario', $form)
                ->with('status', 'Respuesta actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
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
