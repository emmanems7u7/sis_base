<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Session;

use App\Interfaces\RespuestasFormInterface;
use App\Models\RespuestasForm;
use App\Interfaces\CatalogoInterface;
use App\Models\Formulario;
use App\Models\FormularioAsociacion;
use App\Models\RespuestasCampo;
use App\Interfaces\FormularioInterface;
use Illuminate\Support\Facades\DB;
use App\Interfaces\FormLogicInterface;

use App\Interfaces\CamposFormInterface;
use App\Interfaces\RespuestasCampoInterface;
use App\Models\FormLogicCondition;

class RespuestasFormRepository implements RespuestasFormInterface
{
    protected $model;
    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $FormLogicInterface;
    protected $CamposFormRepository;
    protected $RespuestaCampoRepository;


    public function __construct(RespuestasCampoInterface $respuestasCampoInterface, CatalogoInterface $catalogoInterface, FormularioInterface $formularioInterface, FormLogicInterface $formLogicInterface, CamposFormInterface $camposFormInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;
        $this->FormularioRepository = $formularioInterface;
        $this->FormLogicInterface = $formLogicInterface;
        $this->CamposFormRepository = $camposFormInterface;
        $this->RespuestaCampoRepository = $respuestasCampoInterface;

    }


    public function GetHumanRules($rules)
    {
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
        return $humanRules;
    }


    public function validacion($formulario, $campos, $respuestaId = null, $modo = 'store', $prefix = null)
    {


        $rules = [];

        foreach ($campos as $campo) {

            $required = $campo->requerido ? 'required' : 'nullable';

            // ========================================
            // Valor existente
            // ========================================
            $valorExistente = null;

            if ($respuestaId) {
                $valorExistente = DB::table('respuestas_campos')
                    ->where('respuesta_id', $respuestaId)
                    ->where('cf_id', $campo->id)
                    ->value('valor');
            }

            // ========================================
            // Importación desde archivo: omitir multimedia
            // ========================================
            if ($modo === 'archivo' && in_array($campo->tipo, ['CAMPF-023', 'CAMPF-018', 'CAMPF-019'])) {
                continue;
            }

            $fieldName = $prefix
                ? "{$prefix}.{$campo->id}"
                : $campo->id;

            switch ($campo->tipo) {

                case 'CAMPF-012': //text
                case 'CAMPF-014': //textarea
                    $rules[$fieldName] = [$required, 'string', 'max:255'];
                    break;

                case 'CAMPF-013': //number
                    $rules[$fieldName] = [$required, 'numeric'];
                    break;

                case 'CAMPF-015': //checkbox
                    $arrayRules = [$required, 'array'];
                    if ($campo->requerido) {
                        $arrayRules[] = 'min:1';
                    }
                    $rules[$fieldName] = $arrayRules;
                    break;

                case 'CAMPF-016': //radio
                case 'CAMPF-017': //selector
                    $rules[$fieldName] = [$required];
                    break;

                case 'CAMPF-023': //archivo
                case 'CAMPF-018': //imagen
                case 'CAMPF-019': //video

                    $extensiones_permitidas = $this->CatalogoRepository
                        ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);

                    $extensiones = $extensiones_permitidas
                        ->pluck('catalogo_descripcion')
                        ->filter()
                        ->toArray();

                    $extensionesStr = !empty($extensiones)
                        ? implode(',', $extensiones)
                        : '';

                    $fileRules = ['file', 'max:50240']; // 50MB

                    // required solo si:
                    // - es requerido
                    // - no existe archivo guardado
                    if ($campo->requerido && empty($valorExistente)) {
                        $fileRules[] = 'required';
                    } else {
                        $fileRules[] = 'nullable';
                    }

                    if (!empty($extensionesStr)) {
                        $fileRules[] = 'mimes:' . $extensionesStr;
                    }

                    if ($formulario->config['registro_multiple']) {

                        // Para registros dinámicos
                        $rules["registros.*.{$fieldName}"] = $fileRules;

                    } else {

                        // Para formulario normal
                        $rules[$fieldName] = $fileRules;
                    }

                    break;

                case 'CAMPF-024': //color
                    $rules[$fieldName] = [
                        $required,
                        'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
                    ];
                    break;

                case 'CAMPF-025': //email
                    $uniqueRule = $respuestaId
                        ? "unique:respuestas_campos,valor,{$respuestaId},respuesta_id,cf_id,{$campo->id}"
                        : "unique:respuestas_campos,valor,NULL,id,cf_id,{$campo->id}";

                    $rules[$fieldName] = [
                        $required,
                        'email',
                        'max:255',
                        $uniqueRule
                    ];
                    break;

                case 'CAMPF-026': //password
                    $rules[$fieldName] = [
                        $required,
                        'string',
                        'min:6',
                        'max:255'
                    ];
                    break;

                case 'CAMPF-020': //enlace
                    $rules[$fieldName] = [$required, 'url'];
                    break;
                case 'CAMPF-021': //fecha
                    $rules[$fieldName] = [$required, 'date'];
                    break;

                case 'CAMPF-022': //hora
                    $rules[$fieldName] = [$required, 'date_format:H:i'];
                    break;

                default:
                    $rules[$fieldName] = [$required];
            }
        }
        return $rules;
    }

    public function GeneraPlantilla($campos, $form)
    {
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

        $formulario = Formulario::find($form);


        $nombreArchivo = 'plantilla_' . $formulario->nombre . '_' . now() . '.txt';

        return [
            'nombreArchivo' => $nombreArchivo,
            'contenido' => $contenido
        ];
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

    public function normalizarRegistros(array $registros): array
    {
        return array_map(function ($reg) {

            $nuevo = [];

            foreach ($reg as $key => $value) {

                if (is_array($value) && isset($value['preview'])) {
                    $nuevo[$key] = $value['preview'];
                } elseif (is_array($value) && array_is_list($value)) {
                    $nuevo[$key] = array_map(function ($item) {
                        return is_array($item) ? ($item['value'] ?? null) : $item;
                    }, $value);
                } elseif (is_array($value) && isset($value['value'])) {
                    $nuevo[$key] = $value['value'];
                } else {
                    $nuevo[$key] = $value;
                }
            }

            return $nuevo;
        }, $registros);
    }



    public function procesarFormularioNormalDesdeArray($datosFormulario, $form, $campos, $prefix, $evento)
    {

        $respuesta = $this->FormularioRepository->crearRespuesta($form);

        foreach ($campos as $campo) {

            $this->CamposFormRepository->guardarCampo(
                $campo,
                $respuesta->id,
                $datosFormulario,
                $form,
                $prefix
            );
        }

        $filas = $this->RespuestaCampoRepository->filaDesdeArray($respuesta, $datosFormulario, $campos);

        $errores = array_filter($this->FormLogicInterface->ValidarLogica($respuesta, $filas, $evento), fn($msg) => !empty(trim($msg)));
        if (!empty($errores)) {
            DB::rollBack();
            throw new \Exception(implode('<br>', $errores));
        }

        return [
            'respuesta_id' => $respuesta->id,
            'filas' => $filas,
            'form_id' => $form
        ];


    }


    public function procesarFormularioMultipleDesdeArray($datosFormulario, $form, $campos, $prefix, $grupo, $evento)
    {

        $respuestas = [];

        $respuesta = $this->FormularioRepository->crearRespuesta($form);

        if ($grupo) {
            $grupo->respuestas()->attach($respuesta->id);
        }

        foreach ($campos as $campo) {

            $this->CamposFormRepository->guardarCampo(
                $campo,
                $respuesta->id,
                $datosFormulario,
                $form,
                $prefix
            );
        }

        $filas = $this->RespuestaCampoRepository->filaDesdeArray($respuesta, $datosFormulario, $campos);
        $errores = array_filter($this->FormLogicInterface->ValidarLogica($respuesta, $filas, $evento), fn($msg) => !empty(trim($msg)));

        if (!empty($errores)) {
            DB::rollBack();
            throw new \Exception(implode('<br>', $errores));
        }
        $respuestas[] = [
            'respuesta_id' => $respuesta->id,
            'filas' => $filas,
            'form_id' => $form
        ];


        return $respuestas;


    }


    public function cargarFormularioCompleto($formularioId)
    {
        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])->findOrFail($formularioId);

        $camposProcesados = $this->CamposFormRepository->CamposFormCat($formulario->campos);
        $formulario->campos = $camposProcesados;

        return $formulario;
    }
    public function obtenerReglasHumanas($campos)
    {
        $rules = collect();

        foreach ($campos as $campo) {
            $reglasCampo = FormLogicCondition::with([
                'campoCondicion.formulario',
                'campoValor.formulario',
                'action.campoDestino.formulario'
            ])->where('campo_condicion', $campo->id)->get();

            $rules = $rules->merge($reglasCampo);
        }

        return $this->GetHumanRules($rules);
    }


    public function ProcesarArchivo($archivo)
    {

        if (!$archivo->isValid()) {
            [
                "error" => 1,
                "message" => 'Archivo no válido.',

            ];
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
            Session::put('import_last_line', 1);

            return [
                "error" => 0,
                "lineasTotales" => $lineasTotales,

            ];

        } catch (\Exception $e) {
            [
                "error" => 1,
                "message" => 'No se pudo guardar el archivo: ' . $e->getMessage(),

            ];
        }
    }

    public function procesarChunk($form)
    {
        $chunkSize = 1000;

        $path = Session::get('import_file_path');
        $lastLine = Session::get('import_last_line', 1);

        if (!$path) {

            return [
                'error' => 1,
                'message' => 'No hay archivo cargado.',
                'codigo' => 400

            ];
        }

        $handle = fopen(storage_path("app/$path"), 'r');
        if (!$handle) {
            return [
                'error' => 1,
                'message' => 'No se pudo abrir el archivo.',
                'codigo' => 500

            ];
        }

        $campos = $this->CamposFormRepository->GetCamposByForm($form);
        if ($campos->isEmpty()) {
            fclose($handle);

            return [
                'error' => 1,
                'message' => 'No se encontraron campos para este formulario.',
                'codigo' => 400

            ];
        }

        $contador = 0;
        $errores = [];

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

                $respuesta = $this->FormularioRepository->crearRespuesta($form);

                $this->validacion($form, $campos, $respuesta->ID, $modo = 'store');

                foreach ($campos as $campo) {
                    $valor = $dataAsociativa[$campo->nombre] ?? null;
                    if ($valor === null)
                        continue;

                    $tipo = strtolower($campo->campo_nombre);

                    if (in_array($tipo, ['imagen', 'video', 'archivo'])) {
                        $this->FormularioRepository->guardarArchivoGenerico($campo, $respuesta->id, $form, $valor);
                    } else {

                        $this->CamposFormRepository->guardarValorSimple($campo, $respuesta->id, $valor);
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


        return [
            'error' => 0,
            'progreso' => $progreso,
            'finalizado' => $finalizado,
            'errores' => $errores,

        ];

    }


    public function LogicaActualizacion($formId, $formPrefix, $respuestaTarget, $formularioModelo, $request, $evento)
    {

        $campos = $this->CamposFormRepository->GetCamposByForm($formId);

        $rules = $this->validacion($formularioModelo, $campos, null, 'update', $formPrefix);

        $resultado = $this->FormularioRepository->GetData($request, $formPrefix, $rules);

        $datosFormulario = $resultado['datosFormulario'];
        $validator = $resultado['validator'];

        if ($validator->fails()) {

            return ["error" => 1, "content" => $validator];
        }

        $errores = $this->CatalogoRepository->validarOpcionesCatalogo($campos, $datosFormulario, $formPrefix);

        if (!empty($errores)) {

            return ["error" => 1, "content" => $errores];
        }

        $filasOriginales = $this->RespuestaCampoRepository->filaDesdeRespuesta($respuestaTarget, $campos);

        $filas = $this->RespuestaCampoRepository->filaDesdeArray($respuestaTarget, $datosFormulario, $campos);

        $errores = array_filter($this->FormLogicInterface->ValidarLogica($respuestaTarget, $filas, $evento), fn($msg) => !empty(trim($msg)));

        if (!empty($errores)) {

            return ["error" => 1, "content" => implode('<br>', $errores)];
        }

        foreach ($campos as $campo) {

            $this->CamposFormRepository->actualizarCampo($campo, $respuestaTarget->id, $datosFormulario, $formId, $formPrefix);
        }
        return [
            "error" => 0,
            "content" => [
                'respuesta_id' => $respuestaTarget->id,
                'filas' => $filas,
                'filas_originales' => $filasOriginales,
                'form_id' => $formId
            ]
        ];
    }



}
