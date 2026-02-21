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
use App\Models\PlantillaCorreo;
use App\Models\User;
use App\Models\FormLogicAction;
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

    public function CrearRegla($request)
    {
        $acciones = json_decode($request->acciones_json, true);

        $rule = FormLogicRule::create([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);


        $this->guardarAccionesYCondiciones($rule, $acciones);

        return $rule;
    }

    public function EditarRegla($request, $form_logic)
    {
        $acciones = json_decode($request->acciones_json, true);
        $form_logic = FormLogicRule::findOrFail($form_logic);
        $form_logic->update([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'parametros' => $request->parametros ?? null,
        ]);

        // Eliminar acciones existentes y sus condiciones
        $form_logic->actions()->delete();

        $this->guardarAccionesYCondiciones($form_logic, $acciones);
    }
    // Función para guardar acciones y condiciones
    protected function guardarAccionesYCondiciones(FormLogicRule $rule, array $acciones)
    {


        foreach ($acciones as $actionData) {
            // Preparamos los parámetros extra según el tipo de acción
            $parametrosExtra = [];

            switch ($actionData['tipo_accion_id']) {
                case 'TAC-001': // modificar_campo
                    $parametrosExtra = [

                        'operacion' => $actionData['operacion'] ?? 'actualizar',
                        'tipo_valor' => $actionData['tipo_valor'] ?? 'static',
                        'valor' => $actionData['valor'] ?? null,
                        'valor_text' => $actionData['valor_text'] ?? null,
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
                        'campo_ref_id' => $actionData['campo_ref_id'] ?? [],
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? [],
                        'form_ref_text' => $actionData['form_ref_text'] ?? [],
                        'campo_ref_text' => $actionData['campo_ref_text'] ?? [],
                        'operacion_text' => $actionData['operacion_text'] ?? [],
                        'condiciones' => $actionData['condiciones'] ?? [],


                    ];
                    break;


                case 'TAC-005': // crear_registros
                    $parametrosExtra = [
                        'usar_relacion' => $actionData['usar_relacion'] ?? false,
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? '',
                        'formulario_relacion_seleccionado' => $actionData['formulario_relacion_seleccionado'] ?? null,
                        'formulario_relacion_text' => $actionData['formulario_relacion_text'] ?? '',
                        'campos' => $actionData['campos'] ?? [],
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
                        'condiciones' => $actionData['condiciones'] ?? [],
                    ];
                    break;

                case 'TAC-003': // enviar_email


                    $parametrosExtra = [

                        'email_subject' => $actionData['email_subject'] ?? null,
                        'email_body' => $actionData['email_body'] ?? null,
                        'email_template' => $actionData['email_template'] ?? null,

                        'email_usuarios' => $actionData['email_usuarios'] ?? [],

                        'email_roles' => $actionData['email_roles'] ?? [],


                        'condiciones' => $actionData['condiciones'] ?? [],


                    ];
                    break;
                default:
                    // Para otros tipos de acción simplemente guardam todo el actionData
                    $parametrosExtra = $actionData;
                    break;
            }


            if ($actionData['form_ref_id'] == '') {
                $actionData['form_ref_id'] = null;
            }
            // Creamos el registro en FormLogicAction
            $action = FormLogicAction::create([
                'rule_id' => $rule->id,
                'form_ref_id' => $actionData['form_ref_id'] ?? null,
                'tipo_accion' => $actionData['tipo_accion_id'] ?? '',
                'parametros' => $parametrosExtra, // cast array/json en el modelo
            ]);
        }
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
    public function ejecutarLogica(
        RespuestasForm $respuesta,
        $filasSeleccionadas,
        $evento,
        $usuario
    ): array {

        $resultados = [
            'ok' => true,
            'evento' => $evento,
            'form_id' => $respuesta->form_id,
            'respuesta_id' => $respuesta->id,
            'acciones_ejecutadas' => [],
            'errores' => [],
            'mensaje' => ''
        ];

        $reglas = FormLogicRule::where('form_id', $respuesta->form_id)
            ->where('evento', $evento)
            ->where('activo', true)
            ->with([
                'actions' => function ($q) {
                    $q->with('conditions');
                }
            ])
            ->get();

        foreach ($reglas as $regla) {
            foreach ($regla->actions as $action) {

                $resultadoAccion = $this->ejecutarAccion(
                    $respuesta,
                    $filasSeleccionadas,
                    $action,
                    $usuario
                );

                $resultados['acciones_ejecutadas'][] = $resultadoAccion;

                if ($resultadoAccion['ok'] === false) {
                    $resultados['ok'] = false;
                    $resultados['errores'][] = [
                        'accion_id' => $resultadoAccion['accion_id'],
                        'tipo_accion' => $resultadoAccion['tipo_accion'],
                        'errores' => $resultadoAccion['errores'],
                    ];
                }
            }
        }

        // 🧠 Mensaje final resumido
        if ($resultados['ok']) {
            $resultados['mensaje'] =
                'La lógica del formulario se ejecutó correctamente. '
                . count($resultados['acciones_ejecutadas'])
                . ' acciones aplicadas.';
        } else {
            $resultados['mensaje'] =
                'La lógica del formulario se ejecutó con errores en '
                . count($resultados['errores'])
                . ' acción(es).';
        }

        return $resultados;
    }

    public function ValidarLogica(RespuestasForm $respuesta, $filasSeleccionadas, $evento)
    {

        $resultado[] = '';
        $reglas = FormLogicRule::where('form_id', $respuesta->form_id)
            ->where('evento', $evento)
            ->where('activo', true)
            ->with([
                'actions' => function ($q) {
                    $q->with('conditions');
                }
            ])
            ->get();


        foreach ($reglas as $regla) {
            foreach ($regla->actions as $action) {
                $resultado[] = $this->validarAccion($respuesta, $filasSeleccionadas, $action);
            }
        }

        return $resultado;
    }

    public function validarAccion(
        RespuestasForm $respuestaOrigen,
        $filasSeleccionadas,
        $action
    ): string {

        $parametros = $action->parametros ?? [];
        $formDestino = $action->formularioDestino;
        $tipoAccion = $action->tipo_accion;

        $mensaje = '';
        $accionNombre = $this->CatalogoRepository->getNombreCatalogo($tipoAccion);

        switch ($tipoAccion) {

            /* ==============================
             * TAC-001 modificar_campo
             * ============================== */
            case 'TAC-001':

                if (!$formDestino) {
                    $mensaje = "No existe formulario destino para la acción {$action->id}";
                    break;
                }

                $CampoDestinoId = $parametros['campo_ref_id'] ?? null;
                $CampoDestino = CamposForm::find($CampoDestinoId);

                if (!$CampoDestino) {
                    $mensaje = "No existe campo destino para {$accionNombre}, acción {$action->id}";
                    break;
                }

                $respuestaCampoIds = $this->obtenerValorDespuesGuion(
                    $filasSeleccionadas,
                    $CampoDestino->nombre
                );

                if (empty($respuestaCampoIds)) {
                    $mensaje = "No se encontraron valores destino para el campo {$CampoDestino->nombre}";
                    break;
                }

                // Validar valor origen si viene de campo
                if (($parametros['tipo_valor'] ?? 'static') === 'campo') {
                    $campoOrigenId = $parametros['valor'] ?? null;

                    if (!$campoOrigenId) {
                        $mensaje = "Campo origen no definido en acción {$action->id}";
                        break;
                    }

                    $valor = $respuestaOrigen->camposRespuestas()
                        ->where('cf_id', $campoOrigenId)
                        ->value('valor');

                    if ($valor === null || $valor === '') {
                        $mensaje = "El campo origen ({$campoOrigenId}) no tiene valor";
                        break;
                    }
                }

                // Validar condiciones
                foreach ($parametros['condiciones'] ?? [] as $condicion) {

                    $valorOrigen = $respuestaOrigen->camposRespuestas()
                        ->where('cf_id', $condicion['campo_condicion_origen'] ?? null)
                        ->value('valor');

                    if ($valorOrigen === null) {
                        $mensaje = "Condición inválida: campo origen sin valor";
                        break 2;
                    }

                    $destinos = RespuestasCampo::whereIn('id', $respuestaCampoIds)
                        ->where('cf_id', $condicion['campo_condicion_destino'] ?? null)
                        ->get();

                    foreach ($destinos as $destino) {

                        if (
                            !$this->evaluarCondicion(
                                $valorOrigen,
                                $destino->valor,
                                $condicion['operador'] ?? '='
                            )
                        ) {
                            $mensaje = "Condición fallida ({$valorOrigen} {$condicion['operador']} {$destino->valor})";
                            break 3;
                        }
                    }
                }

                break;

            /* ==============================
             * TAC-003 enviar_email
             * ============================== */
            case 'TAC-003':

                if (empty($parametros['email_subject'])) {
                    $mensaje = "TAC-003: El asunto del correo es obligatorio";
                    break;
                }

                if (
                    empty($parametros['email_body']) &&
                    empty($parametros['email_template'])
                ) {
                    $mensaje = "TAC-003: Debe existir cuerpo o plantilla de correo";
                    break;
                }

                if (
                    empty($parametros['email_usuarios']) &&
                    empty($parametros['email_roles'])
                ) {
                    $mensaje = "TAC-003: No se definieron destinatarios";
                    break;
                }

                if (!ConfCorreo::first()) {
                    $mensaje = "TAC-003: No existe configuración de correo";
                    break;
                }

                break;

            /* ==============================
             * TAC-005 crear_registros
             * ============================== */
            case 'TAC-005':

                if (!$formDestino) {
                    $mensaje = "No existe formulario destino para la acción {$action->id}";
                    break;
                }

                if (empty($parametros['campos'])) {
                    $mensaje = "No se definieron campos para crear registros";
                    break;
                }

                break;
        }

        if ($mensaje !== '') {
            Log::warning("VALIDACIÓN FALLIDA | Action {$action->id} | {$mensaje}");
        }

        return $mensaje;
    }

    private function evaluarCondicion($origen, $destino, $operador): bool
    {
        switch ($operador) {
            case '=':
                return $origen == $destino;
            case '!=':
                return $origen != $destino;
            case '>':
                return (float) $origen > (float) $destino;
            case '<':
                return (float) $origen < (float) $destino;
            case '>=':
                return (float) $origen >= (float) $destino;
            case '<=':
                return (float) $origen <= (float) $destino;
            case 'in':
                $valores = is_array($destino) ? $destino : explode(',', (string) $destino);
                return in_array($origen, $valores);
            default:
                return false;
        }
    }



    public function ejecutarAccion(
        RespuestasForm $respuestaOrigen,
        $filasSeleccionadas,
        $action,
        $usuario
    ) {
        try {

            $parametros = $action->parametros ?? [];
            $formDestino = $action->formularioDestino;
            $tipoAccion = $action->tipo_accion;


            $audit = [
                'accion_id' => $action->id,
                'tipo_accion' => $tipoAccion,
                'detalle' => [],
                'mensaje' => '',
                'errores' => [],
            ];

            switch ($tipoAccion) {

                /* =====================================================
                 * TAC-001 → modificar_campo
                 * ===================================================== */
                case 'TAC-001':

                    $CampoDestino = CamposForm::find($parametros['campo_ref_id']);
                    $respuestaCampoIds = $this->obtenerValorDespuesGuion(
                        $filasSeleccionadas,
                        $CampoDestino->nombre
                    );

                    // Determinar valor final
                    if (($parametros['tipo_valor'] ?? 'static') === 'campo') {

                        $campoOrigenId = $parametros['valor'];
                        $valor = $respuestaOrigen->camposRespuestas()
                            ->where('cf_id', $campoOrigenId)
                            ->value('valor');

                    } else {
                        $valor = $action->valor;
                    }

                    DB::transaction(function () use ($respuestaCampoIds, $action, $valor, $CampoDestino) {

                        $operacion = $action->OperacionCatalogo ?? 'actualizar';

                        $respuestasDestino = RespuestasCampo::whereIn('id', $respuestaCampoIds)->get();

                        foreach ($respuestasDestino as $campoResp) {

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
                                    if ((float) $valor !== 0.0) {
                                        $campoResp->valor /= (float) $valor;
                                    }
                                    break;

                                case 'asignar':
                                case 'actualizar':
                                    $campoResp->valor = $valor;
                                    break;

                                case 'copiar':
                                    $campoOrigen = RespuestasCampo::find($valor);
                                    $campoResp->valor = $campoOrigen?->valor;
                                    break;

                                case 'concatenar':
                                    $campoResp->valor = ($campoResp->valor ?? '') . $valor;
                                    break;

                                case 'limpiar':
                                    $campoResp->valor = null;
                                    break;

                                case 'incrementar_fecha':
                                    $campoResp->valor = \Carbon\Carbon::parse($campoResp->valor)
                                        ->addDays((int) $valor)
                                        ->format('Y-m-d');
                                    break;

                                case 'decrementar_fecha':
                                    $campoResp->valor = \Carbon\Carbon::parse($campoResp->valor)
                                        ->subDays((int) $valor)
                                        ->format('Y-m-d');
                                    break;
                            }

                            $campoResp->save();
                        }

                        Log::info(
                            "TAC-001 ejecutado | Action {$action->id} | Campo {$CampoDestino->nombre}"
                        );

                        $audit['detalle'][] = [
                            'tac' => 'TAC-001',
                            'campo_destino_id' => $CampoDestino->id,
                            'campo_destino_nombre' => $CampoDestino->nombre,
                            'operacion' => $action->OperacionCatalogo,
                            'valor_aplicado' => $valor,
                            'respuestas_modificadas' => $respuestaCampoIds,
                        ];
                    });



                    $audit['mensaje'] =
                        "Se modificaron " . count($respuestaCampoIds) .
                        " registros del campo {$CampoDestino->nombre}";
                    break;


                /* =====================================================
                 * TAC-003 → enviar_email
                 * ===================================================== */
                case 'TAC-003': // enviar_email dinámico

                    $subject = $parametros['email_subject'];
                    $bodyBase = $parametros['email_body'] ?? null;
                    $templateId = $parametros['email_template'] ?? null;
                    $usuariosIds = $parametros['email_usuarios'] ?? [];
                    $rolesIds = $parametros['email_roles'] ?? [];

                    // Obtener destinatarios
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

                    // Cargar plantilla si existe
                    $htmlPlantilla = null;

                    if ($templateId) {
                        $plantilla = PlantillaCorreo::find($templateId);
                        $ruta = public_path('plantillas_correos/' . $plantilla->archivo);
                        $htmlPlantilla = file_get_contents($ruta);
                    }

                    $conf = ConfCorreo::first();
                    $mailer = new DynamicMailer($conf);

                    foreach ($usuariosDestino as $userDestino) {

                        $body = $bodyBase;

                        // Inyectar cuerpo en plantilla
                        if ($htmlPlantilla) {

                            libxml_use_internal_errors(true);

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML(
                                mb_convert_encoding($htmlPlantilla, 'HTML-ENTITIES', 'UTF-8')
                            );

                            $xpath = new \DOMXPath($dom);
                            $contenedor = $xpath->query("//*[@id='contenido']")->item(0);

                            if ($contenedor) {

                                while ($contenedor->firstChild) {
                                    $contenedor->removeChild($contenedor->firstChild);
                                }

                                $tmpDoc = new \DOMDocument();
                                $tmpDoc->loadHTML(
                                    mb_convert_encoding('<div>' . $bodyBase . '</div>', 'HTML-ENTITIES', 'UTF-8')
                                );

                                $tmpBody = $tmpDoc->getElementsByTagName('div')->item(0);

                                foreach ($tmpBody->childNodes as $child) {
                                    $contenedor->appendChild(
                                        $dom->importNode($child, true)
                                    );
                                }

                                $body = $dom->saveHTML();
                            }
                        }

                        // Reemplazo de variables [campo]
                        preg_match_all('/\[(.*?)\]/', $body, $matches);
                        $variables = $matches[1] ?? [];

                        foreach ($variables as $variable) {

                            $valor = null;

                            // 1️⃣ Campos del formulario
                            $campo = CamposForm::where('nombre', $variable)->first();

                            if ($campo) {

                                $valorUsuario = $respuestaOrigen->camposRespuestas()
                                    ->where('cf_id', $campo->id)
                                    ->value('valor');

                                if (!empty($campo->categoria_id) || !empty($campo->form_ref_id)) {
                                    $valor = $this->FormularioRepository
                                        ->obtenerValorReal($campo, $valorUsuario);
                                } else {
                                    $valor = $valorUsuario;
                                }
                            }

                            // 2️⃣ Atributos del usuario
                            if ($valor === null && in_array($variable, $userDestino->getFillable())) {
                                $valor = $userDestino->{$variable};
                            }

                            // 3️⃣ Reemplazar solo si hay valor
                            if ($valor !== null && $valor !== '') {
                                $body = str_replace("[$variable]", $valor, $body);
                            }
                        }

                        // Enviar correo
                        $mailer->send(
                            $conf->from_address,
                            new \App\Mail\CorreoDinamico(
                                $subject,
                                $body,
                                $userDestino->email
                            )
                        );

                        Log::info(
                            "TAC-003 ejecutado | Action {$action->id} | Enviado a {$userDestino->email}"
                        );

                        $audit['detalle'][] = [
                            'tac' => 'TAC-003',
                            'email' => $userDestino->email,
                            'usuario_id' => $userDestino->id,
                            'subject' => $subject,
                            'plantilla_id' => $templateId,
                            'variables_reemplazadas' => $variables,
                        ];
                    }
                    $audit['mensaje'] =
                        'Se enviaron ' . $usuariosDestino->count() . ' correos correctamente';
                    break;

                /* =====================================================
                 * TAC-005 → crear_registros
                 * ===================================================== */
                case 'TAC-005':

                    $campos = $parametros['campos'];
                    $filtrosRelacion = $parametros['filtros_relacion'] ?? [];
                    $usarRelacion = $parametros['usar_relacion'] ?? false;

                    $campoRelacion = CamposForm::where(
                        'form_ref_id',
                        $parametros['formulario_relacion_seleccionado']
                    )->first();

                    $registrosOrigen = collect([$respuestaOrigen]);

                    if ($usarRelacion) {

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
                    }


                    DB::transaction(function () use ($registrosOrigen, $campos, $campoRelacion, $respuestaOrigen, $action, $usuario) {


                        foreach ($registrosOrigen as $registroOrigen) {

                            $respuestaDestino = RespuestasForm::create([
                                'form_id' => $action->form_ref_id,
                                'actor_id' => $usuario
                            ]);

                            foreach ($campos as $campo) {

                                $valorFinal = ($campo['usar_origen'] ?? false)
                                    ? $respuestaOrigen->camposRespuestas()
                                        ->where('cf_id', $campo['campo_origen_id'])
                                        ->value('valor')
                                    : ($campo['valor_destino'] ?? null);

                                RespuestasCampo::create([
                                    'respuesta_id' => $respuestaDestino->id,
                                    'cf_id' => $campo['campo_id'],
                                    'valor' => $valorFinal,
                                ]);
                            }

                            RespuestasCampo::create([
                                'respuesta_id' => $respuestaDestino->id,
                                'cf_id' => $campoRelacion->id,
                                'valor' => $registroOrigen->id,
                            ]);

                            Log::info(
                                "TAC-005 ejecutado | Action {$action->id} | Respuesta {$respuestaDestino->id}"
                            );
                            $audit['detalle'][] = [
                                'tac' => 'TAC-005',
                                'respuesta_origen_id' => $registroOrigen->id,
                                'respuesta_destino_id' => $respuestaDestino->id,
                                'campos_creados' => collect($campos)->pluck('campo_id'),
                            ];
                        }
                        $audit['mensaje'] =
                            'Se crearon ' . count($audit['detalle']) . ' registros relacionados';

                    });


                    break;


            }

            $huboError = false;


            return [
                'ok' => !$huboError,
                'accion_id' => $audit['accion_id'],
                'tipo_accion' => $audit['tipo_accion'],
                'mensaje' => $audit['mensaje'],
                'detalle' => $audit['detalle'],
                'errores' => $audit['errores'],
            ];

        } catch (\Throwable $e) {
            $audit['errores'][] = [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ];

            $audit['mensaje'] = 'La acción presentó errores durante su ejecución';
            Log::error('Error ejecutando acción', [
                'accion_id' => $audit['accion_id'],
                'tipo_accion' => $audit['tipo_accion'],
                'error' => $e->getMessage(),
            ]);
        }
    }







}
