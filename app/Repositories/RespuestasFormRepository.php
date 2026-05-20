<?php

namespace App\Repositories;

use App\Interfaces\RespuestasFormInterface;
use App\Models\RespuestasForm;
use App\Interfaces\CatalogoInterface;
use App\Models\Formulario;
use App\Models\ModuloFormularioParalelo;
use App\Models\RespuestasCampo;

class RespuestasFormRepository implements RespuestasFormInterface
{
    protected $model;
    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $catalogoInterface, )
    {

        $this->CatalogoRepository = $catalogoInterface;
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

    public function fila($request)
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

    public function filaDesdeArray($respuesta, array $registroData, $campos)
    {
        $filasSeleccionadas = [];
        $relations = [];

        // Mapear campos por nombre para acceso rápido (como en la versión anterior)
        $mapCampos = collect($campos)->keyBy('nombre');

        $filasSeleccionadas['formulario_id'] = $respuesta->form_id;
        $filasSeleccionadas['respuesta_id'] = $respuesta->id;

        foreach ($registroData as $nombreCampo => $valor) {
            preg_match('/\[(.*?)\]/', $nombreCampo, $match);
            $nombreLimpio = $match[1] ?? $nombreCampo;

            $campo = $mapCampos[$nombreLimpio] ?? null;

            if (!$campo) {
                continue;
            }

            $esReferencia = !empty($campo->form_ref_id);

            if ($esReferencia) {
                // Usar resolverRelacionCompleta existente
                $relacion = $this->resolverRelacionCompleta($valor, $campo, $respuesta);

                $textoLiteral = $valor;

                if ($relacion) {
                    // buscar primer valor humano dentro del array
                    if (is_array($relacion) && !isset($relacion['formulario_id'])) {
                        // Es un array de múltiples relaciones
                        foreach ($relacion as $rel) {
                            foreach ($rel as $k => $v) {
                                if (
                                    $k !== 'formulario_id' &&
                                    $k !== 'respuesta_id' &&
                                    !is_array($v)
                                ) {
                                    $textoLiteral = (is_array($valor) ? implode(',', $valor) : $valor) . ' | ' . $v;
                                    break 2;
                                }
                            }
                        }
                    } else {
                        // Es una relación simple
                        foreach ($relacion as $k => $v) {
                            if (
                                $k !== 'formulario_id' &&
                                $k !== 'respuesta_id' &&
                                !is_array($v)
                            ) {
                                $textoLiteral = $valor . ' | ' . $v;
                                break;
                            }
                        }
                    }

                    if (is_array($valor)) {
                        $relations[$campo->form_ref_id][] = $relacion;
                    } else {
                        $relations[$campo->form_ref_id] = $relacion;
                    }
                }

                // Usar ID del campo como clave (como en filaDesdeRespuesta)
                $filasSeleccionadas[$campo->id] = $this->limpiarDuplicado($textoLiteral);
                continue;
            }

            // Valor normal (no referencia) - usar ID como clave
            $filasSeleccionadas[$campo->id] = $valor;
        }

        if (!empty($relations)) {
            $filasSeleccionadas['relations'] = $relations;
        }

        return $filasSeleccionadas;
    }
    public function filaDesdeRespuesta($respuesta, $campos)
    {
        $filasSeleccionadas = [];
        $relations = [];

        $filasSeleccionadas['formulario_id'] = $respuesta->form_id;
        $filasSeleccionadas['respuesta_id'] = $respuesta->id;

        foreach ($campos as $campo) {

            $respuestaCampo = $respuesta->camposRespuestas
                ->firstWhere('cf_id', $campo->id);

            if (!$respuestaCampo)
                continue;

            $valor = $respuestaCampo->valor;
            $nombre = $campo->id;

            $esReferencia = !empty($campo->form_ref_id);

            if ($esReferencia) {

                $relacion = $this->resolverRelacionCompleta($valor, $campo, $respuesta);

                // 🔥 obtener fila real de la relación (esto es lo clave)
                $textoLiteral = $valor;

                if ($relacion) {

                    // buscar primer valor humano dentro del array (ANTES lo tenías así)
                    foreach ($relacion as $k => $v) {

                        if (
                            $k !== 'formulario_id' &&
                            $k !== 'respuesta_id' &&
                            !is_array($v)
                        ) {
                            $textoLiteral = $valor . ' | ' . $v;
                            break;
                        }
                    }

                    if (is_array($valor)) {
                        $relations[$campo->form_ref_id][] = $relacion;
                    } else {
                        $relations[$campo->form_ref_id] = $relacion;
                    }
                }

                $filasSeleccionadas[$nombre] = $this->limpiarDuplicado($textoLiteral);

                continue;
            }

            $filasSeleccionadas[$nombre] = $valor;
        }

        if (!empty($relations)) {
            $filasSeleccionadas['relations'] = $relations;
        }

        return $filasSeleccionadas;
    }
    private function limpiarDuplicado($texto)
    {
        if (!is_string($texto)) {
            return $texto;
        }

        $partes = explode(' | ', $texto);

        if (count($partes) < 2) {
            return $texto;
        }

        $izquierda = trim($partes[0]);
        $derecha = trim($partes[1]);

        // 🔥 eliminar lo que está entre corchetes
        $derechaSinCorchetes = preg_replace('/\s*\[.*?\]/', '', $derecha);
        $derechaSinCorchetes = trim($derechaSinCorchetes);

        // 🔥 comparar limpio vs limpio
        if ($izquierda === $derechaSinCorchetes) {
            return $izquierda;
        }

        return $texto;
    }
    private function resolverRelacionCompleta($valor, $campo, $respuesta)
    {
        $resolver = function ($id) use ($campo, $respuesta) {

            $fila = RespuestasForm::with('camposRespuestas.campo')->find($id);

            if ($fila)
                return $this->mapearFila($fila);

            $res = $this->obtenerRelacionMultiple(
                $respuesta->form_id,
                $campo->form_ref_id
            );

            $campoOrigen = collect($res['formula'] ?? [])
                ->first(fn($item) => ($item['tipo'] ?? null) === 'campo');

            $campoIdOrigen = $campoOrigen['campo_id'] ?? null;

            if (!$campoIdOrigen)
                return null;

            $newId = RespuestasCampo::where('cf_id', $campoIdOrigen)
                ->where('valor', $id)
                ->pluck('respuesta_id')
                ->first();

            $fila = RespuestasForm::with('camposRespuestas.campo')->find($newId);

            return $fila ? $this->mapearFila($fila) : null;
        };

        // múltiple
        if (is_array($valor)) {

            $data = [];

            foreach ($valor as $id) {
                if (!is_numeric($id))
                    continue;

                $rel = $resolver($id);

                if ($rel)
                    $data[] = $rel;
            }

            return $data;
        }

        // simple
        return $resolver($valor);
    }

    private function mapearFila($fila)
    {
        $data = [];

        $data['formulario_id'] = $fila->form_id;
        $data['respuesta_id'] = $fila->id;

        foreach ($fila->camposRespuestas as $cr) {
            $data[$cr->campo->id] = $cr->valor . ' [' . $cr->id . ']';
        }


        return $data;
    }
    public function obtenerRelacionMultiple($form_id, $form_id2)
    {
        $form_id = (string) $form_id;
        $form_id2 = (string) $form_id2;

        $paralelo = ModuloFormularioParalelo::whereJsonContains('formularios', ['id' => $form_id])
            ->whereJsonContains('formularios', ['id' => $form_id2])
            ->first();


        foreach ($paralelo->config ?? [] as $config) {

            if (($config['relacion_multiple'] ?? 0) != 1) {
                continue;
            }

            $destinoForm = $config['destino']['form'] ?? null;

            $campoRelacion = collect($config['formula'] ?? [])
                ->first(fn($x) => ($x['tipo'] ?? null) === 'campo');

            $origenForm = $campoRelacion['form'] ?? null;

            if (!$destinoForm || !$origenForm) {
                continue;
            }

            $formsRelacionados = [
                (string) $destinoForm,
                (string) $origenForm
            ];

            // Validación final
            if (
                in_array($form_id, $formsRelacionados, true) &&
                in_array($form_id2, $formsRelacionados, true)
            ) {
                return $config;
            }
        }

        return null;
    }
    public function validacion($formulario, $campos, $respuestaId = null, $modo = 'store', $prefix = null)
    {


        $rules = [];

        foreach ($campos as $campo) {

            $tipo = strtolower($campo->campo_nombre);
            $required = $campo->requerido ? 'required' : 'nullable';

            // ========================================
            // Valor existente (solo edición)
            // ========================================
            $valorExistente = null;

            if ($respuestaId) {
                $valorExistente = \DB::table('respuestas_campos')
                    ->where('respuesta_id', $respuestaId)
                    ->where('cf_id', $campo->id)
                    ->value('valor');
            }

            // ========================================
            // Importación desde archivo: omitir multimedia
            // ========================================
            if ($modo === 'archivo' && in_array($tipo, ['archivo', 'imagen', 'video'])) {
                continue;
            }

            $fieldName = $prefix
                ? "{$prefix}.{$campo->nombre}"
                : $campo->nombre;

            switch ($tipo) {

                case 'text':
                case 'textarea':
                    $rules[$fieldName] = [$required, 'string', 'max:255'];
                    break;

                case 'number':
                    $rules[$fieldName] = [$required, 'numeric'];
                    break;

                case 'checkbox':
                    $arrayRules = [$required, 'array'];
                    if ($campo->requerido) {
                        $arrayRules[] = 'min:1';
                    }
                    $rules[$fieldName] = $arrayRules;
                    break;

                case 'radio':
                case 'selector':
                    $rules[$fieldName] = [$required];
                    break;

                case 'archivo':
                case 'imagen':
                case 'video':

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

                case 'color':
                    $rules[$fieldName] = [
                        $required,
                        'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
                    ];
                    break;

                case 'email':
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

                case 'password':
                    $rules[$fieldName] = [
                        $required,
                        'string',
                        'min:6',
                        'max:255'
                    ];
                    break;

                case 'enlace':
                    $rules[$fieldName] = [$required, 'url'];
                    break;

                case 'fecha':
                    $rules[$fieldName] = [$required, 'date'];
                    break;

                case 'hora':
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

    public function EliminarArchivos($respuesta)
    {
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

    }


}
