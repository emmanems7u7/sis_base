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

    /*
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


        */


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

                        foreach ($registrosOrigen->take(10) as $registroOrigen) {

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
