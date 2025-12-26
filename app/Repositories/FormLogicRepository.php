<?php

namespace App\Repositories;

use App\Interfaces\FormLogicInterface;
use App\Models\CamposForm;
use App\Models\FormLogicRule;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Interfaces\CatalogoInterface;
use App\Models\ConfCorreo;
use App\Models\Formulario;
use App\Models\PlantillaCorreo;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

use App\Services\DynamicMailer;
class FormLogicRepository implements FormLogicInterface
{
    protected $CatalogoRepository;
    protected $FormularioRepository;
    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioRepository $formularioRepository,

    ) {
        $this->FormularioRepository = $formularioRepository;
        $this->CatalogoRepository = $catalogoInterface;



    }
    function obtenerValorDespuesGuion(array $filasSeleccionadas, string $nombreCampo)
    {
        $resultados = [];

        foreach ($filasSeleccionadas as $grupo => $campos) {

            // Si es un array de arrays (como "ventas")
            if (is_array($campos) && isset($campos[0]) && is_array($campos[0])) {
                foreach ($campos as $subcampos) {
                    if (isset($subcampos[$nombreCampo])) {
                        $partes = explode('-', $subcampos[$nombreCampo]);
                        $resultados[] = trim($partes[1] ?? null);
                    }
                }
            }

            // Si es un array asociativo simple (como "inve")
            elseif (is_array($campos) && isset($campos[$nombreCampo])) {
                $partes = explode('-', $campos[$nombreCampo]);
                $resultados[] = trim($partes[1] ?? null);
            }
        }

        return $resultados;
    }
    public function ejecutarLogica(RespuestasForm $respuesta, $filasSeleccionadas, string $evento)
    {
        $formularioId = $respuesta->form_id;


        $reglas = FormLogicRule::where('form_id', $formularioId)
            ->where('evento', $evento)
            ->where('activo', true)
            ->with([
                'actions' => function ($q) {
                    $q->with('conditions');
                }
            ])
            ->get();

        $mensajes = [];

        foreach ($reglas as $regla) {
            foreach ($regla->actions as $action) {
                $mensajes[] = $this->ejecutarAccion($respuesta, $filasSeleccionadas, $action);
            }
        }

        return $mensajes;


    }

    public function ejecutarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action)
    {


        $parametros = $action->parametros ?? [];

        $formDestino = $action->formularioDestino;

        $tipoAccion = $action->tipo_accion;



        $mensaje = '';

        $accion = $this->CatalogoRepository->getNombreCatalogo($tipoAccion);

        switch ($tipoAccion) {

            case 'TAC-001':


                if (!$formDestino) {
                    $mensaje = "No existe formulario destino para la acción {$action->id}";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                // modificar_campo
                $CampoDestinoId = $parametros['campo_ref_id'] ?? null;
                $CampoDestino = CamposForm::find($CampoDestinoId);

                if (!$CampoDestino) {
                    $mensaje = "No existe campo destino para {$accion}, acción {$action->id}";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                $respuesta_campo_ids = $this->obtenerValorDespuesGuion($filasSeleccionadas, $CampoDestino->nombre);
                if (empty($respuesta_campo_ids)) {
                    $mensaje = "No se encontraron valores después del guion para el campo {$CampoDestino->nombre}";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                // Determinar valor

                if (($parametros['tipo_valor'] ?? 'static') === 'campo') {
                    $campoOrigenId = $parametros['valor'] ?? null;


                    $respuestasFiltradas = $respuestaOrigen
                        ->camposRespuestas()
                        ->where('cf_id', $campoOrigenId)
                        ->first();

                    $campoOrigen = RespuestasCampo::where('cf_id', $campoOrigenId)->where('respuesta_id', 229713)->first();

                    $valor = $respuestasFiltradas->valor ? $campoOrigenId : null;


                    if ($valor === null) {
                        $mensaje = "El campo origen con ID {$campoOrigenId} no tiene valor";
                        Log::warning($mensaje);
                        return $mensaje;
                    }
                } else {

                    $valor = $action->valor;

                }

                // Validar condiciones
                foreach ($parametros['condiciones'] ?? [] as $condicion) {
                    $valor_origen = $respuestaOrigen->camposRespuestas()
                        ->where('cf_id', $condicion['campo_condicion_origen'] ?? null)
                        ->first()
                        ->valor ?? null;

                    $valores_destino = RespuestasCampo::whereIn('id', $respuesta_campo_ids)
                        ->where('cf_id', $condicion['campo_condicion_destino'] ?? null)
                        ->get();

                    foreach ($valores_destino as $va) {
                        $valor_destino = $va->valor;
                        switch ($condicion['operador'] ?? '=') {
                            case '=':
                                if ($valor_origen != $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} != {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} != {$valor_destino}");
                                }
                                break;
                            case '!=':
                                if ($valor_origen == $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} == {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} == {$valor_destino}");
                                }
                                break;
                            case '>':
                                if ((float) $valor_origen <= (float) $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} <= {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} <= {$valor_destino}");
                                }
                                break;
                            case '<':
                                if ((float) $valor_origen >= (float) $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} >= {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} >= {$valor_destino}");
                                }
                                break;
                            case '>=':
                                if ((float) $valor_origen < (float) $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} < {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} < {$valor_destino}");
                                }
                                break;
                            case '<=':
                                if ((float) $valor_origen > (float) $valor_destino) {
                                    $mensaje .= "Condición fallida: {$valor_origen} > {$valor_destino}<br>";
                                    Log::warning("Condición fallida: {$valor_origen} > {$valor_destino}");
                                }
                                break;
                            case 'in':
                                $valores = is_array($valor_destino) ? $valor_destino : explode(',', (string) $valor_destino);
                                if (!in_array($valor_origen, $valores)) {
                                    $mensaje .= "Condición fallida: {$valor_origen} NO está en [" . implode(',', $valores) . "]<br>";
                                    Log::warning("Condición fallida: {$valor_origen} NO está en [" . implode(',', $valores) . "]");
                                }
                                break;
                            default:
                                Log::warning("Operador desconocido: {$condicion['operador']}");
                                $mensaje .= "Operador desconocido: {$condicion['operador']}<br>";
                                break;
                        }
                    }
                }

                if ($mensaje != '')
                    return $mensaje;

                // Ejecutar acción según 'operacion' dentro de parámetros
                DB::transaction(function () use ($respuesta_campo_ids, $parametros, $CampoDestino, $action, $valor) {


                    $operacion = $action->OperacionCatalogo ?? 'actualizar';
                    $respuestaDestinos = RespuestasCampo::whereIn('id', $respuesta_campo_ids)->get();

                    foreach ($respuestaDestinos as $campoResp) {
                        switch ($operacion) {
                            case 'sumar':
                                $campoResp->valor += (float) $valor;
                                break;
                            case 'restar':
                                $campoResp->valor -= (float) $valor;
                                break;
                            case 'multiplicar':
                                $campoResp->valor *= (float) $valor;
                                break;
                            case 'dividir':
                                if ($valor != 0)
                                    $campoResp->valor /= (float) $valor;
                                break;
                            case 'actualizar':
                            case 'asignar':
                                $campoResp->valor = $valor;
                                break;
                            case 'copiar':
                                $campoOrigen = RespuestasCampo::find($valor);
                                $campoResp->valor = $campoOrigen ? $campoOrigen->valor : null;
                                break;
                            case 'concatenar':
                                $campoResp->valor = ($campoResp->valor ?? '') . $valor;
                                break;
                            case 'limpiar':
                                $campoResp->valor = null;
                                break;
                            case 'incrementar_fecha':
                                $campoResp->valor = $campoResp->valor ? \Carbon\Carbon::parse($campoResp->valor)->addDays((int) $valor)->format('Y-m-d') : null;
                                break;
                            case 'decrementar_fecha':
                                $campoResp->valor = $campoResp->valor ? \Carbon\Carbon::parse($campoResp->valor)->subDays((int) $valor)->format('Y-m-d') : null;
                                break;
                        }
                        $campoResp->save();
                        Log::info("Regla ejecutada: Action ID {$action->id}, Campo {$CampoDestino->nombre}, Valor: {$campoResp->valor}");
                    }

                });


                break;

            case 'TAC-005': // crear_registros

                if (!$formDestino) {
                    $mensaje = "No existe formulario destino para la acción {$action->id}";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                $mensaje = '';

                // 1️⃣ Validación básica
                if (empty($parametros['campos'])) {
                    $mensaje = "No se definieron campos para crear registros en acción {$action->id}";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                $campos = $parametros['campos'];
                $filtrosRelacion = $parametros['filtros_relacion'] ?? [];
                $usarRelacion = $parametros['usar_relacion'] ?? false;

                //dump($campos);
                //dump($filtrosRelacion);
                //dump($usarRelacion);
                //selecciona el campo de relacion existente, a futuro posibilidad de que sea mas de una la relaciòn requerida   
                $campoRelacion = CamposForm::where('form_ref_id', $parametros['formulario_relacion_seleccionado'])->first();

                // 2️⃣ Obtener registros origen
                // Por defecto se usa la respuesta actual
                $registrosOrigen = $respuestaOrigen;

                $respuestasOrigen = RespuestasForm::find($registrosOrigen->id);

                $respuestasCampos = $respuestasOrigen->camposRespuestas;



                $CampoDestinoId = $parametros['campo_ref_id'] ?? null;
                $CampoDestino = CamposForm::find($CampoDestinoId);

                if ($usarRelacion) {


                    $form_relacion = Formulario::find($parametros['formulario_relacion_seleccionado']);


                    $query = RespuestasForm::query()
                        ->where('form_id', $parametros['formulario_relacion_seleccionado']);

                    foreach ($filtrosRelacion as $filtro) {

                        $query->whereHas('camposRespuestas', function ($q) use ($filtro, $respuestaOrigen) {

                            $valorOrigen = $respuestaOrigen->camposRespuestas()
                                ->where('cf_id', $filtro['campoOrigen'])
                                ->value('valor');
                            $q->where('cf_id', $filtro['campoRelacion'])
                                ->where('valor', $filtro['condicion'], $valorOrigen);

                        });


                    }

                    $registrosOrigen = $query->get();

                    /*
                    $registrosOrigen->each(function ($registro) {
                        $campos = $registro->camposRespuestas; // colección de RespuestasCampo

                        dump([
                            'respuesta_id' => $registro->id,
                            'campos' => $campos->map(function ($campo) {
                                return [
                                    'cf_id' => $campo->cf_id,
                                    'valor' => $campo->valor,
                                ];
                            })->toArray()
                        ]);
                    });
*/


                }


                if ($registrosOrigen->isEmpty()) {
                    $mensaje = "No se encontraron registros relacionados para crear registros (Action {$action->id})";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                // 3️⃣ Crear registros destino
                DB::transaction(function () use ($respuestaOrigen, $registrosOrigen, $campos, $action, $campoRelacion) {




                    foreach ($registrosOrigen->take(10) as $registroOrigen) {

                        // Crear respuesta destino
                        $respuestaDestino = RespuestasForm::create([
                            'form_id' => $action->form_ref_id,
                            'actor_id' => auth()->id() ?? null
                        ]);


                        //dump($registroOrigen);

                        foreach ($campos as $campo) {

                            // Determinar valor final
                            if ($campo['usar_origen'] ?? false) {

                                $valorFinal = $respuestaOrigen->camposRespuestas()
                                    ->where('cf_id', $campo['campo_origen_id'] ?? null)
                                    ->first()
                                    ->valor ?? null;

                            } else {
                                $valorFinal = $campo['valor_destino'] ?? null;
                            }
                            //dump($valorFinal);

                            //dump($campo);
                            $PRUEBA = RespuestasCampo::create([
                                'respuesta_id' => $respuestaDestino->id,
                                'cf_id' => $campo['campo_id'],
                                'valor' => $valorFinal,
                            ]);
                        }


                        $PRUEBA = RespuestasCampo::create([
                            'respuesta_id' => $respuestaDestino->id,
                            'cf_id' => $campoRelacion->id,
                            'valor' => $registroOrigen->id,
                        ]);
                        Log::info(
                            "TAC-005 ejecutado | Action {$action->id} | Respuesta creada {$respuestaDestino->id}"
                        );
                    }
                });


                break;
            case 'TAC-003': // enviar_email dinámico

                $mensaje = '';

                // Estructura esperada en $parametros
                $subject = $parametros['email_subject'] ?? null;
                $bodyBase = $parametros['email_body'] ?? null;
                $templateId = $parametros['email_template'] ?? null;
                $usuariosIds = $parametros['email_usuarios'] ?? [];
                $rolesIds = $parametros['email_roles'] ?? [];

                // 1️⃣ Validaciones básicas
                if (!$subject) {
                    $mensaje = "TAC-003: El asunto del correo es obligatorio (Action {$action->id})";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                if (!$bodyBase && !$templateId) {
                    $mensaje = "TAC-003: Debe existir mensaje o plantilla (Action {$action->id})";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                if (empty($usuariosIds) && empty($rolesIds)) {
                    $mensaje = "TAC-003: No se definieron destinatarios (Action {$action->id})";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                // 2️⃣ Obtener destinatarios
                $usuariosDestino = collect();

                if (!empty($usuariosIds)) {
                    $usuariosDestino = $usuariosDestino->merge(
                        User::whereIn('id', $usuariosIds)->get()
                    );
                }

                if (!empty($rolesIds)) {
                    $usuariosDestino = $usuariosDestino->merge(
                        User::whereHas('roles', function ($q) use ($rolesIds) {
                            $q->whereIn('id', $rolesIds);
                        })->get()
                    );
                }

                $usuariosDestino = $usuariosDestino->unique('id')->values();

                if ($usuariosDestino->isEmpty()) {
                    $mensaje = "TAC-003: No se encontraron usuarios destino (Action {$action->id})";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                // 3️⃣ Cargar plantilla si existe
                $htmlPlantilla = null;
                if ($templateId) {
                    $plantilla = PlantillaCorreo::find($templateId);

                    if (!$plantilla || !$plantilla->estado) {
                        $mensaje = "TAC-003: Plantilla no válida o inactiva (Action {$action->id})";
                        Log::warning($mensaje);
                        return $mensaje;
                    }

                    $ruta = public_path('plantillas_correos/' . $plantilla->archivo);

                    if (!file_exists($ruta)) {
                        $mensaje = "TAC-003: Archivo de plantilla no encontrado ({$plantilla->archivo})";
                        Log::warning($mensaje);
                        return $mensaje;
                    }

                    $htmlPlantilla = file_get_contents($ruta);
                }

                // 4️⃣ Obtener configuración de correo
                $conf = ConfCorreo::first();
                if (!$conf) {
                    $mensaje = "TAC-003: Configuración de correo no encontrada";
                    Log::warning($mensaje);
                    return $mensaje;
                }

                $mailer = new DynamicMailer($conf);

                // 5️⃣ Envío de correo por cada usuario
                foreach ($usuariosDestino as $userDestino) {

                    // 5.1 Construir cuerpo del correo
                    $body = $bodyBase;

                    if ($htmlPlantilla) {
                        libxml_use_internal_errors(true);
                        $dom = new \DOMDocument('1.0', 'UTF-8');
                        $dom->loadHTML(mb_convert_encoding($htmlPlantilla, 'HTML-ENTITIES', 'UTF-8'));
                        $xpath = new \DOMXPath($dom);
                        $contenedor = $xpath->query("//*[@id='contenido']")->item(0);

                        if (!$contenedor) {
                            Log::warning("TAC-003: No se encontró div #contenido en la plantilla (Action {$action->id})");
                            continue;
                        }

                        // Limpiar contenido previo
                        while ($contenedor->firstChild) {
                            $contenedor->removeChild($contenedor->firstChild);
                        }

                        // Crear un fragmento temporal para cargar el HTML de CKEditor
                        $tmpDoc = new \DOMDocument();
                        $tmpDoc->loadHTML(mb_convert_encoding('<div>' . $bodyBase . '</div>', 'HTML-ENTITIES', 'UTF-8'));
                        $tmpBody = $tmpDoc->getElementsByTagName('div')->item(0);

                        foreach ($tmpBody->childNodes as $child) {
                            $contenedor->appendChild($dom->importNode($child, true));
                        }

                        $body = $dom->saveHTML();
                    }
                    // 5.2 Reemplazo de variables [campo]
                    preg_match_all('/\[(.*?)\]/', $body, $matches);
                    $variables = $matches[1] ?? [];

                    foreach ($variables as $variable) {

                        $valor = null; // IMPORTANTE: null, no ''

                        // 1️⃣ Buscar en campos del formulario
                        $campo = CamposForm::where('nombre', $variable)->first();

                        if ($campo) {
                            $valorUsuario = $respuestaOrigen->camposRespuestas()
                                ->where('cf_id', $campo->id)
                                ->value('valor');

                            if (!empty($campo->categoria_id) || !empty($campo->form_ref_id)) {
                                $valor = $this->FormularioRepository->obtenerValorReal($campo, $valorUsuario);
                            } else {
                                $valor = $valorUsuario;
                            }
                        }

                        // 2️⃣ Buscar en atributos del usuario destino
                        if ($valor === null && in_array($variable, $userDestino->getFillable())) {
                            $valor = $userDestino->{$variable} ?? null;
                        }

                        // 3️⃣ SOLO reemplazar si hay valor
                        if ($valor !== null && $valor !== '') {
                            $body = str_replace("[$variable]", $valor, $body);
                        }

                        // ❗ Si no hay valor → se mantiene [variable]
                    }

                    // 5.3 Enviar correo usando DynamicMailer
                    try {
                        $mailer->send($conf->from_address, new \App\Mail\CorreoDinamico($subject, $body, $userDestino->email));
                        Log::info("TAC-003 ejecutado | Action {$action->id} | Correo enviado a: {$userDestino->email}");
                    } catch (\Throwable $e) {
                        $mensaje = "TAC-003 Error al enviar correo (Action {$action->id}) a {$userDestino->email}: " . $e->getMessage();
                        Log::error($mensaje);
                        return $mensaje;
                    }
                }

                break;



            // Agregar más acciones según sea necesario
        }

        return $mensaje;
    }

}
