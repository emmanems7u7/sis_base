<?php

namespace App\Repositories;

use App\Interfaces\RespuestasFormInterface;
use App\Models\RespuestasForm;
use App\Interfaces\CatalogoInterface;
use App\Models\Formulario;

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

    public function validacion($campos, $respuestaId = null, $modo = 'store')
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

                    $extensiones_permitidas = $this->CatalogoRepository
                        ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);

                    $extensiones = $extensiones_permitidas
                        ->pluck('catalogo_descripcion')
                        ->filter()
                        ->toArray();

                    $extensionesStr = !empty($extensiones)
                        ? implode(',', $extensiones)
                        : '';

                    $fileRules = ['file', 'max:50240']; // 50 MB

                    // required SOLO si:
                    // - el campo es requerido
                    // - NO existe archivo guardado
                    if ($campo->requerido && empty($valorExistente)) {
                        $fileRules[] = 'required';
                    } else {
                        $fileRules[] = 'nullable';
                    }

                    if (!empty($extensionesStr)) {
                        $fileRules[] = 'mimes:' . $extensionesStr;
                    }

                    $rules[$campo->nombre] = $fileRules;
                    break;

                case 'color':
                    $rules[$campo->nombre] = [
                        $required,
                        'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
                    ];
                    break;

                case 'email':
                    $uniqueRule = $respuestaId
                        ? "unique:respuestas_campos,valor,{$respuestaId},respuesta_id,cf_id,{$campo->id}"
                        : "unique:respuestas_campos,valor,NULL,id,cf_id,{$campo->id}";

                    $rules[$campo->nombre] = [
                        $required,
                        'email',
                        'max:255',
                        $uniqueRule
                    ];
                    break;

                case 'password':
                    $rules[$campo->nombre] = [
                        $required,
                        'string',
                        'min:6',
                        'max:255'
                    ];
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
