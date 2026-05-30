<?php

namespace App\Repositories;

use App\Interfaces\CamposFormInterface;
use App\Interfaces\FormLogicInterface;
use App\Models\CamposForm;
use App\Models\FormLogicRule;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Interfaces\CatalogoInterface;
use App\Interfaces\FormularioInterface;
use App\Interfaces\RespuestasCampoInterface;
use App\Models\AuditoriaAccion;
use App\Models\ConfCorreo;
use App\Models\PlantillaCorreo;
use App\Models\User;
use App\Models\FormLogicAction;
use App\Models\Formulario;
use App\Notifications\LogicaFormularioFinalizada;
use App\Services\DynamicMailer;
use App\Jobs\EjecutarLogicaFormulario;
use App\Models\ModuloFormularioParalelo;

class FormLogicRepository implements FormLogicInterface
{
    protected $CatalogoRepository;
    protected $FormularioRepository;
    protected $CamposFormRepository;
    protected $RespuestasCampoRepository;



    public function __construct(
        CatalogoInterface $catalogoInterface,
        FormularioInterface $formularioRepository,
        RespuestasCampoInterface $respuestasCampoInterface,
        CamposFormInterface $camposFormInterface


    ) {
        $this->FormularioRepository = $formularioRepository;
        $this->CatalogoRepository = $catalogoInterface;
        $this->CamposFormRepository = $camposFormInterface;
        $this->RespuestasCampoRepository = $respuestasCampoInterface;

    }

    public function CrearRegla($request)
    {

        $acciones = json_decode($request->acciones_json, true);

        $rule = FormLogicRule::create([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id_disparador,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'segundo_plano' => $request->has('segundo_plano'),
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
            'form_id' => $request->formulario_id_disparador,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'segundo_plano' => $request->has('segundo_plano'),

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

                        'form_origen_id' => $actionData['form_origen_id'] ?? [],
                        'operacion' => $actionData['operacion'] ?? 'actualizar',
                        'tipo_valor' => $actionData['tipo_valor'] ?? 'static',
                        'valor' => $actionData['valor'] ?? null,
                        'valor_text' => $actionData['valor_text'] ?? null,
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
                        'campo_ref_id' => $actionData['campo_ref_id'] ?? [],
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? [],
                        'form_ref_id' => $actionData['form_ref_id'] ?? [],
                        'form_ref_text' => $actionData['form_ref_text'] ?? [],
                        'campo_ref_text' => $actionData['campo_ref_text'] ?? [],
                        'operacion_rev' => $actionData['operacion_rev'] ?? 0,
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

                case 'TAC-006': // eliminar_registro

                    $parametrosExtra = [

                        'form_origen_id' => $actionData['form_origen_id'] ?? [],
                        'form_ref_id' => $actionData['form_ref_id'] ?? [],

                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? '',
                        'filtros_relacion' => $actionData['filtros_relacion'] ?? [],
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


    function GetResultadoByCampoOrigen(array $filasSeleccionadas, string $nombreCampo, $form_id = null, $valor = null): array
    {


        $resultado = [
            'formulario_id' => null,
            'respuesta_id' => null,
            'campo_id' => null,
            'valor' => null,
            'from_relation' => false,
        ];

        /*
        |--------------------------------------------------------------------------
        | FUNCIÓN LIMPIAR VALOR
        |--------------------------------------------------------------------------
        */

        $limpiarValor = function ($valor) {

            if ($valor === null || $valor === '') {
                return null;
            }

            if (is_string($valor)) {

                // quitar [123]
                $valor = preg_replace('/\[[^\]]*\]\s*/', '', $valor);

                // espacios múltiples
                $valor = preg_replace('/\s+/', ' ', $valor);

                // trim final
                $valor = trim($valor);
            }

            return $valor;
        };

        /*
        |--------------------------------------------------------------------------
        | CASO 0: RELATION POR FORM_ID
        |--------------------------------------------------------------------------
        */

        if ($form_id !== null && isset($filasSeleccionadas['relations'][$form_id])) {

            $relation = $filasSeleccionadas['relations'][$form_id];

            if (
                array_key_exists($nombreCampo, $relation) &&
                !is_array($relation[$nombreCampo])
            ) {

                $resultado['formulario_id'] = $relation['formulario_id'] ?? null;
                $resultado['respuesta_id'] = $relation['respuesta_id'] ?? null;
                $resultado['campo_id'] = $nombreCampo;
                $resultado['valor'] = $limpiarValor($relation[$nombreCampo]);
                $resultado['from_relation'] = true;

                return $resultado;
            }
        } else {
            // SI NO ENCONTRÓ NADA EN MEMORIA, CONSULTAR BD COMO ÚLTIMO RECURSO


        }

        /*
        |--------------------------------------------------------------------------
        | CASO 1: ARRAY SIMPLE PRINCIPAL
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists($nombreCampo, $filasSeleccionadas) &&
            !is_array($filasSeleccionadas[$nombreCampo])
        ) {

            $resultado['formulario_id'] = $filasSeleccionadas['formulario_id'] ?? null;
            $resultado['respuesta_id'] = $filasSeleccionadas['respuesta_id'] ?? null;
            $resultado['campo_id'] = $nombreCampo;
            $resultado['valor'] = $limpiarValor($filasSeleccionadas[$nombreCampo]);
            $resultado['from_relation'] = false;

            return $resultado;
        }

        /*
        |--------------------------------------------------------------------------
        | CASO 2: ESTRUCTURA COMPLEJA
        |--------------------------------------------------------------------------
        */

        foreach ($filasSeleccionadas as $grupo => $campos) {

            /*
            |--------------------------------------------------------------------------
            | ARRAY DE ARRAYS
            |--------------------------------------------------------------------------
            */

            if (
                is_array($campos) &&
                isset($campos[0]) &&
                is_array($campos[0])
            ) {

                foreach ($campos as $subcampos) {

                    if (!isset($subcampos[$nombreCampo])) {
                        continue;
                    }

                    $resultado['formulario_id'] = $subcampos['formulario_id'] ?? null;
                    $resultado['respuesta_id'] = $subcampos['respuesta_id'] ?? null;
                    $resultado['campo_id'] = $nombreCampo;
                    $resultado['valor'] = $limpiarValor($subcampos[$nombreCampo]);
                    $resultado['from_relation'] = true;

                    return $resultado;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ARRAY SIMPLE INTERNO
            |--------------------------------------------------------------------------
            */ elseif (
                is_array($campos) &&
                array_key_exists($nombreCampo, $campos)
            ) {

                $resultado['formulario_id'] = $campos['formulario_id'] ?? null;
                $resultado['respuesta_id'] = $campos['respuesta_id'] ?? null;
                $resultado['campo_id'] = $nombreCampo;
                $resultado['valor'] = $limpiarValor($campos[$nombreCampo]);
                $resultado['from_relation'] = true;

                return $resultado;
            }
        }


        return $resultado;
    }


    public function ejecutarLogica(
        $reglas,
        $respuestas,
        $evento,
        $usuario
    ): array {

        $esMultiple = $respuestas instanceof \Illuminate\Support\Collection;

        $respuestas = $esMultiple
            ? $respuestas
            : collect([$respuestas]);

        $respuesta = $respuestas->first();

        $form = Formulario::find($respuesta->form_id);

        $resultados = [
            'ok' => true,
            'evento' => $evento,
            'form_id' => $respuesta->form_id,
            'respuesta_id' => $respuesta->id,
            'acciones_ejecutadas' => [],
            'errores' => [],
            'mensaje' => ''
        ];


        foreach ($reglas as $regla) {
            foreach ($regla->actions as $action) {

                $resultadoAccion = $this->ejecutarAccion(
                    $respuestas,
                    $action,
                    $usuario,
                    $form->config['registro_multiple'] ?? false
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

    public function ValidarLogica($respuesta, $filasSeleccionadas, $evento)
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
                $msg = $this->validarAccion($respuesta, $filasSeleccionadas, $action);

                if (!empty($msg)) {
                    $resultado[] = trim($msg);
                }
            }
        }

        $mensaje = collect($resultado)
            ->unique()
            ->values()
            ->toArray();
        return $mensaje;
    }
    private function ObtenerMensajeValidador($mensaje, $respuestaOrigen, $respuesta_id_destino)
    {
        preg_match_all('/Cdestino_(\d+)/', $mensaje, $destinos);
        $valoresCdestino = $destinos[1] ?? [];

        preg_match_all('/Corigen_(\d+)/', $mensaje, $origenes);
        $valoresCorigen = $origenes[1] ?? [];

        $p = [];
        $o = null;

        if (!empty($valoresCdestino)) {
            $p = RespuestasCampo::whereIn('respuesta_id', $respuesta_id_destino)
                ->whereIn('cf_id', $valoresCdestino)
                ->pluck('valor', 'cf_id')
                ->toArray();
        }

        if (!empty($valoresCorigen)) {
            $o = $respuestaOrigen->camposRespuestas()
                ->whereIn('cf_id', $valoresCorigen)
                ->value('valor');
        }

        $mensaje = preg_replace_callback('/Cdestino_(\d+)/', function ($match) use ($p) {
            return $p[$match[1]] ?? $match[0];
        }, $mensaje);

        $mensaje = preg_replace_callback('/Corigen_(\d+)/', function ($match) use ($o) {
            return $o ?? $match[0];
        }, $mensaje);

        return $mensaje;
    }

    private function ValidarCondicionesIgualdad($condicionesIgual, $filasSeleccionadas, $parametros, $respuestaOrigen)
    {
        $mensaje = '';

        foreach ($condicionesIgual as $condicion) {

            if (isset($condicion['tipo_condicion']) && $condicion['tipo_condicion'] === 'form_valor') {

                continue;
            }

            $valorEvaluar = $this->resolverValorEvaluar($condicion['campo_condicion_origen'], $filasSeleccionadas, $parametros['form_origen_id']);

            $resultado = $this->resolverValorEvaluar($condicion['campo_condicion_destino'], $filasSeleccionadas, $parametros['form_ref_id'], $valorEvaluar['valor']);
            if ($valorEvaluar['valor'] === null || $valorEvaluar['valor'] === '') {

                $mensaje = "El valor origen está vacío";

                break;
            }

            if ($resultado['valor'] === null || $resultado['valor'] === '') {

                $mensaje_f = '';

                if (isset($condicion['mensaje'])) {

                    $mensaje_f = $this->ObtenerMensajeValidador($condicion['mensaje'], $respuestaOrigen, $resultado['respuesta_id']);
                }

                $mensaje = !empty($mensaje_f) ? $mensaje_f : "No se esta cumpliendo la validación asignada";

                break;
            }
        }


        return $mensaje;
    }
    private function ValidarOtrasCondiciones($otrasCondiciones, $filasSeleccionadas, $parametros, $respuestaOrigen, $valor_principal)
    {

        $mensaje = '';

        foreach ($otrasCondiciones as $condicion) {

            if (!isset($condicion['tipo_condicion']) || $condicion['tipo_condicion'] !== 'form_valor') {

                $resultado = $this->resolverValorEvaluar($condicion['campo_condicion_destino'], $filasSeleccionadas, $parametros['form_ref_id']);

                if (!$this->evaluarCondicion($valor_principal, $resultado['valor'], $condicion['operador'])) {
                    $mensaje_f = $this->ObtenerMensajeValidador($condicion['mensaje'], $respuestaOrigen, $resultado['respuesta_id']);

                    if (!empty($mensaje_f)) {

                        $mensaje = $mensaje_f;

                    } else {

                        $mensaje = "No se esta cumpliendo la validación asignada ({$valor_principal} {$condicion['operador']} {$resultado['valor']})";
                    }

                    break;
                }

            } else {

                $resultado = $this->resolverValorEvaluar($condicion['campo_condicion'], $filasSeleccionadas, $parametros['form_ref_id']);

                $valor_consulta = $condicion['valor'];

                if (!$this->evaluarCondicion($valor_consulta, $resultado['valor'], $condicion['operador'])) {

                    $mensaje_f = '';

                    if (isset($condicion['mensaje'])) {

                        $mensaje_f = $this->ObtenerMensajeValidador($condicion['mensaje'], $respuestaOrigen, $resultado['respuesta_id']);
                    }

                    if (!empty($mensaje_f)) {

                        $mensaje = $mensaje_f;

                    } else {

                        $mensaje = "No se esta cumpliendo la validación asignada ({$valor_consulta} {$condicion['operador']} {$resultado['valor']})";
                    }

                    break;
                }
            }
        }

        return $mensaje;
    }

    public function validarAccion(RespuestasForm $respuestaOrigen, $filasSeleccionadas, $action): string
    {

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

                // Validar valor origen si viene de campo

                $condicionesIgual = [];
                $otrasCondiciones = [];
                foreach ($parametros['condiciones'] ?? [] as $condicion) {
                    if (($condicion['operador'] ?? '=') === '=') {
                        if (!isset($condicion['tipo_condicion']) || $condicion['tipo_condicion'] !== 'form_valor') {

                            $condicionesIgual[] = $condicion;
                        } else {
                            $otrasCondiciones[] = $condicion;

                        }
                    } else {
                        $otrasCondiciones[] = $condicion;
                    }
                }

                $mensaje = $this->ValidarCondicionesIgualdad($condicionesIgual, $filasSeleccionadas, $parametros, $respuestaOrigen);

                if ($mensaje != '') {
                    break;
                }

                // OBTENER EL VALOR PRINCIPAL PARA EVALUAR LA CONDICION
                $CampoDestinoId = $parametros['campo_ref_id'] ?? null;
                $tipoValor = $parametros['tipo_valor'] ?? null;
                $valorRaw = $parametros['valor'] ?? null;

                $CampoDestino = CamposForm::find($CampoDestinoId);



                if (!$CampoDestino) {
                    $mensaje = "No existe campo destino para {$accionNombre}, acción {$action->id}";
                    break;
                }


                // RESOLVER CAMPO ORIGEN SEGÚN tipo_valor

                $campoOrigenId = null;

                if ($tipoValor == 'campo') {


                    $campoOrigenId = $valorRaw;

                    if (!$campoOrigenId) {
                        $mensaje = "Campo origen no definido en acción {$action->id}";
                        break;
                    }

                } elseif ($tipoValor == 'static') {

                    // valor directo (no depende de campos)
                    $valor_principal = $valorRaw;

                    if ($valor_principal === null || $valor_principal === '') {
                        $mensaje = "Valor estático no definido en acción {$action->id}";
                        break;
                    }

                } else {

                    $mensaje = "tipo_valor inválido en acción {$action->id}";
                    break;
                }


                //CASO: VALOR DESDE OTRO CAMPO

                if ($tipoValor == 'campo') {

                    // Obtener valores desde filas seleccionadas
                    $resultado = $this->GetResultadoByCampoOrigen($filasSeleccionadas, $campoOrigenId);

                    if (collect($resultado)->filter()->isEmpty()) {

                        $resultado = $this->GetResultadoByCampoOrigen($filasSeleccionadas, $campoOrigenId, $parametros['form_origen_id']);

                    }

                    $tieneValoresValidos = is_array($resultado) && !blank($resultado['valor'] ?? null);

                    if (!$tieneValoresValidos) {

                        $mensaje = "No se encontraron valores válidos en campo ID {$campoOrigenId}";
                        break;
                    }

                    // Obtener valor real desde la respuesta origen
                    $valor_principal = $resultado['valor'] ?? null;
                    if ($valor_principal === null || $valor_principal === '') {
                        $mensaje = "El campo origen ({$campoOrigenId}) no tiene valor";
                        break;
                    }
                }

                $mensaje = $this->ValidarOtrasCondiciones($otrasCondiciones, $filasSeleccionadas, $parametros, $respuestaOrigen, $valor_principal);
                if ($mensaje != '') {
                    break;
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

            case 'TAC-006':

                // Validar valor origen si viene de campo

                $condicionesIgual = [];
                $otrasCondiciones = [];
                foreach ($parametros['condiciones'] ?? [] as $condicion) {
                    if (($condicion['operador'] ?? '=') === '=') {
                        if (!isset($condicion['tipo_condicion']) || $condicion['tipo_condicion'] !== 'form_valor') {

                            $condicionesIgual[] = $condicion;
                        } else {
                            $otrasCondiciones[] = $condicion;

                        }
                    } else {
                        $otrasCondiciones[] = $condicion;
                    }
                }





                $mensaje = $this->ValidarCondicionesIgualdad($condicionesIgual, $filasSeleccionadas, $parametros, $respuestaOrigen);

                if ($mensaje != '') {
                    break;
                }



                // AGREGAR LOGICA DE OTRAS CONDICIONES

                break;

        }

        if ($mensaje !== '') {
            Log::warning("VALIDACIÓN FALLIDA | Action {$action->id} | {$mensaje}");
        }

        return $mensaje;
    }

    private function resolverValorEvaluar($campo_condicion, $filasSeleccionadas, $form_id, $valorOrigen = null)
    {
        $resultado = [
            'formulario_id' => null,
            'respuesta_id' => null,
            'campo_id' => null,
            'valor' => null,
            'from_relation' => false,
        ];

        if (!isset($form_id) || blank($form_id) || is_array($form_id)) {

            return $resultado;
        }

        //CASO 1: MISMO FORMULARIO


        if ($form_id == $filasSeleccionadas['formulario_id']) {

            $resultado['formulario_id'] = $filasSeleccionadas['formulario_id'];
            $resultado['respuesta_id'] = $filasSeleccionadas['respuesta_id'] ?? null;
            $resultado['campo_id'] = $campo_condicion;
            $resultado['valor'] = $filasSeleccionadas[$campo_condicion] ?? null;
            $resultado['from_relation'] = false;
        }

        //CASO 2: RELATION 
        else if (isset($filasSeleccionadas['relations'][$form_id])) {

            $relation = $filasSeleccionadas['relations'][$form_id];

            $resultado['formulario_id'] = $relation['formulario_id'] ?? null;
            $resultado['respuesta_id'] = $relation['respuesta_id'] ?? null;
            $resultado['campo_id'] = $campo_condicion;
            $resultado['valor'] = $relation[$campo_condicion] ?? null;
            $resultado['from_relation'] = true;

        } else {
            $resultado = [
                'formulario_id' => null,
                'respuesta_id' => null,
                'campo_id' => null,
                'valor' => null,
                'from_relation' => false,
            ];
        }

        //LIMPIEZA DE VALOR

        if (is_string($resultado['valor'])) {

            if (str_contains($resultado['valor'], '|')) {

                $partes = explode('|', $resultado['valor']);

                $resultado['valor'] = isset($partes[1]) ? trim($partes[1]) : trim($partes[0]);
            }

            $resultado['valor'] = preg_replace('/\[[^\]]*\]\s*/', '', $resultado['valor']);
            $resultado['valor'] = preg_replace('/\s+/', ' ', $resultado['valor']);
            $resultado['valor'] = trim($resultado['valor']);
        }


        //SI NO ENCONTRÓ VALOR CONSULTAR BD COMO ÚLTIMO RECURSO


        if (($resultado['valor'] === null || $resultado['valor'] === '') && $valorOrigen) {
            $registro = RespuestasCampo::where('valor', $valorOrigen)
                ->where('cf_id', $campo_condicion)
                ->first();

            if ($registro) {

                $resultado = [
                    'formulario_id' => $form_id,
                    'respuesta_id' => $registro->respuesta_id,
                    'campo_id' => $registro->cf_id,
                    'valor' => $registro->valor,
                    'from_relation' => false,
                ];
            }
        }

        return $resultado;
    }

    private function evaluarCondicion($origen, $valor, $operador): bool
    {
        switch ($operador) {
            case '=':
                return $origen == $valor;
            case '!=':
                return $origen != $valor;
            case '>':
                return (float) $origen > (float) $valor;
            case '<':
                return (float) $origen < (float) $valor;
            case '>=':
                return (float) $origen >= (float) $valor;
            case '<=':
                return (float) $origen <= (float) $valor;
            case 'in':
                $valores = is_array($valor) ? $valor : explode(',', (string) $valor);
                return in_array($origen, $valores);
            default:
                return false;
        }
    }
    public function ejecutarAccion($respuestas, $action, $usuario, $esMultiple)
    {
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

            Log::info($tipoAccion);

            switch ($tipoAccion) {

                // TAC-001 modificar_campo
                case 'TAC-001':


                    $condicionesIgual = [];
                    foreach ($parametros['condiciones'] ?? [] as $condicion) {
                        if (($condicion['operador'] ?? '=') === '=') {
                            if (!isset($condicion['tipo_condicion']) || $condicion['tipo_condicion'] !== 'form_valor') {

                                $condicionesIgual[] = $condicion;
                            } else {
                                $otrasCondiciones[] = $condicion;

                            }
                        } else {
                            $otrasCondiciones[] = $condicion;
                        }
                    }
                    $resultado = $this->EjecutarModificarCampo($respuestas, $esMultiple, $parametros, $action, $condicionesIgual);

                    if (!$resultado['success']) {
                        break;
                    }

                    break;

                //TAC-003 enviar_email
                case 'TAC-003': // enviar_email dinámico

                    $resultado = $this->EjecutarEnviarCorreo($respuestas, $esMultiple, $parametros);

                    if (!$resultado['success']) {
                        break;
                    }

                    break;

                //TAC-005 crear_registros
                case 'TAC-005':

                    $resultado = $this->EjecutarCrearRelacionados($respuestas, $esMultiple, $parametros, $action, $usuario);

                    if (!$resultado['success']) {
                        break;
                    }

                    break;

                //TAC-006 eliminar_registro

                case 'TAC-006':

                    $respuesta = $respuestas->first();


                    $filasSeleccionadas = $respuesta->filasSeleccionadas;

                    $condicionesIgual = [];
                    foreach ($parametros['condiciones'] ?? [] as $condicion) {
                        if (($condicion['operador'] ?? '=') === '=') {
                            if (!isset($condicion['tipo_condicion']) || $condicion['tipo_condicion'] !== 'form_valor') {

                                $condicionesIgual[] = $condicion;
                            } else {
                                $otrasCondiciones[] = $condicion;

                            }
                        } else {
                            $otrasCondiciones[] = $condicion;
                        }
                    }


                    $resultados = [];

                    $respuestaIds = $this->GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros);

                    $respuestaIds = array_values($respuestaIds);



                    if (!collect($respuestaIds)->filter()->isEmpty()) {

                        $respuestas = RespuestasForm::whereIn('id', $respuestaIds)->get();

                        foreach ($respuestas as $respuesta) {
                            $this->LogicaEliminarRespuesta($respuesta);

                        }

                    }

                    break;
            }

            $huboError = !$resultado['success'];

            return [
                'ok' => !$huboError,
                'accion_id' => $action->id,
                'tipo_accion' => $action->tipo_accion,
                'mensaje' => $resultado['audit']['mensaje'] ?? '',
                'detalle' => $resultado['audit']['detalle'] ?? [],
                'errores' => [],
            ];

        } catch (\Throwable $e) {

            Log::error('Error ejecutando acción', [
                'accion_id' => $action->id,
                'tipo_accion' => $action->tipo_accion,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'accion_id' => $action->id,
                'tipo_accion' => $action->tipo_accion,
                'mensaje' => 'La acción presentó errores durante su ejecución',
                'detalle' => [],
                'errores' => [
                    [
                        'mensaje' => $e->getMessage(),
                        'linea' => $e->getLine(),
                        'archivo' => $e->getFile(),
                    ]
                ],
            ];
        }
    }
    private function GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros)
    {
        foreach ($condicionesIgual as $condicion) {

            if (
                isset($condicion['tipo_condicion']) &&
                $condicion['tipo_condicion'] === 'form_valor'
            ) {
                continue;
            }

            $valorEvaluar = $this->resolverValorEvaluar(
                $condicion['campo_condicion_origen'],
                $filasSeleccionadas,
                $parametros['form_origen_id']
            );

            $resultado = $this->resolverValorEvaluar(
                $condicion['campo_condicion_destino'],
                $filasSeleccionadas,
                $parametros['form_ref_id'],
                $valorEvaluar['valor']
            );

            if ($valorEvaluar['valor'] === null || $valorEvaluar['valor'] === '') {
                break;
            }

            if ($resultado['valor'] === null || $resultado['valor'] === '') {
                break;
            }

            $respuestaIds[$resultado['respuesta_id']] = $resultado['respuesta_id'];
        }
        return $respuestaIds ?? [];
    }

    private function EjecutarCrearRelacionados($respuestas, $esMultiple, $parametros, $action, $usuario)
    {

        $audit = [
            'detalle' => []
        ];

        $respuestasCollection = $esMultiple
            ? $respuestas
            : collect([$respuestas->first() ?? $respuestas]);

        foreach ($respuestasCollection as $respuestaOrigen) {

            $campos = $parametros['campos'];
            $filtrosRelacion = $parametros['filtros_relacion'] ?? [];
            $usarRelacion = $parametros['usar_relacion'] ?? false;

            $campoRelacion = CamposForm::where(
                'form_ref_id',
                $parametros['formulario_relacion_seleccionado']
            )->first();

            if (!$campoRelacion) {
                return [
                    'success' => false,
                    'audit' => [
                        'mensaje' => 'No se encontró el campo de relación.',
                        'detalle' => []
                    ]
                ];
            }

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

            DB::transaction(function () use ($registrosOrigen, $campos, $campoRelacion, $respuestaOrigen, $action, $usuario, &$audit) {

                foreach ($registrosOrigen as $registroOrigen) {

                    $respuestaDestino = RespuestasForm::create([
                        'form_id' => $action->form_ref_id,
                        'actor_id' => $usuario
                    ]);

                    foreach ($campos as $campo) {

                        $valor_principal = ($campo['usar_origen'] ?? false)
                            ? $respuestaOrigen->camposRespuestas()
                                ->where('cf_id', $campo['campo_origen_id'])
                                ->value('valor')
                            : ($campo['valor_destino'] ?? null);

                        RespuestasCampo::create([
                            'respuesta_id' => $respuestaDestino->id,
                            'cf_id' => $campo['campo_id'],
                            'valor' => $valor_principal,
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

                        'action_id' => $action->id,

                        'respuesta_origen_id' => $respuestaOrigen->id,
                        'respuesta_relacion_id' => $registroOrigen->id,
                        'respuesta_destino_id' => $respuestaDestino->id,

                        'formulario_destino_id' => $action->form_ref_id,

                        'campo_relacion_id' => $campoRelacion->id,

                        'campos_creados' => collect($campos)
                            ->pluck('campo_id')
                            ->values()
                            ->toArray(),

                        'mensaje' => "TAC-005 ejecutado | Action {$action->id} | Respuesta {$respuestaDestino->id}",
                    ];
                }

                $audit['mensaje'] =
                    'Se crearon ' .
                    count($audit['detalle']) .
                    ' registros relacionados';
            });
        }

        return [
            'success' => true,
            'audit' => $audit
        ];
    }
    private function EjecutarEnviarCorreo($respuestas, $esMultiple, $parametros)
    {

        $audit = [
            'detalle' => []
        ];

        $subject = $parametros['email_subject'];
        $bodyBase = $parametros['email_body'] ?? null;
        $templateId = $parametros['email_template'] ?? null;
        $usuariosIds = $parametros['email_usuarios'] ?? [];
        $rolesIds = $parametros['email_roles'] ?? [];

        $respuestasCollection = $esMultiple
            ? $respuestas
            : collect([$respuestas->first() ?? $respuestas]);

        // DESTINATARIOS

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
            return [
                'success' => false,
                'audit' => [
                    'mensaje' => 'No se encontraron destinatarios.',
                    'detalle' => []
                ]
            ];
        }

        // PLANTILLA

        $htmlPlantilla = null;

        if ($templateId) {

            $plantilla = PlantillaCorreo::find($templateId);

            if (!$plantilla) {
                return [
                    'success' => false,
                    'audit' => [
                        'mensaje' => 'La plantilla seleccionada no existe.',
                        'detalle' => []
                    ]
                ];
            }

            $ruta = public_path('plantillas_correos/' . $plantilla->archivo);

            if (!file_exists($ruta)) {
                return [
                    'success' => false,
                    'audit' => [
                        'mensaje' => 'No se encontró el archivo de la plantilla.',
                        'detalle' => []
                    ]
                ];
            }

            $htmlPlantilla = file_get_contents($ruta);
        }

        $conf = ConfCorreo::first();
        $mailer = new DynamicMailer($conf);

        // ENVÍO

        foreach ($usuariosDestino as $userDestino) {

            $body = $bodyBase;

            // VARIABLES NORMALES [campo]

            if (!$esMultiple) {

                preg_match_all('/\[(.*?)\]/', $body, $matches);
                $variables = $matches[1] ?? [];

                foreach ($variables as $variable) {

                    $valor = null;

                    $campos = CamposForm::where('nombre', $variable)->get();

                    foreach ($campos as $campo) {

                        $valorUsuario = $respuestasCollection->first()
                            ->camposRespuestas()
                            ->where('cf_id', $campo->id)
                            ->value('valor');

                        if (!empty($campo->categoria_id) || !empty($campo->form_ref_id)) {

                            $valor = $this->FormularioRepository
                                ->obtenerValorReal($campo, $valorUsuario);

                        } else {

                            $valor = $valorUsuario;
                        }

                        if ($valor !== null && $valor !== '') {
                            $body = str_replace("[$variable]", $valor, $body);
                        }
                    }
                }
            }

            // ITERADORES (SOLO MULTIPLE)

            if ($esMultiple) {

                $registros = $respuestasCollection->map(function ($respuesta) {

                    $fila = [];

                    foreach ($respuesta->camposRespuestas as $cr) {

                        $campo = $cr->campo;
                        $valorUsuario = $cr->valor;
                        $valor_principal = $valorUsuario;

                        Log::info($cr);
                        Log::info($valorUsuario);
                        Log::info($campo);

                        if (!empty($campo->categoria_id) || !empty($campo->form_ref_id)) {

                            $valor_principal = $this->FormularioRepository
                                ->obtenerValorReal($campo, $valorUsuario);

                            Log::info($valor_principal);
                        }

                        $fila[$campo->id] = $valor_principal;
                    }

                    return $fila;
                });

                // TABLA

                if (str_contains($body, '[iterar_tabla]') && $registros->isNotEmpty()) {

                    $tabla = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;">';
                    $tabla .= '<thead><tr>';

                    foreach (array_keys($registros->first()) as $columna) {
                        $tabla .= '<th>' . ucfirst($columna) . '</th>';
                    }

                    $tabla .= '</tr></thead><tbody>';

                    foreach ($registros as $registro) {
                        $tabla .= '<tr>';

                        foreach ($registro as $valor) {
                            $tabla .= '<td>' . $valor . '</td>';
                        }

                        $tabla .= '</tr>';
                    }

                    $tabla .= '</tbody></table>';

                    $body = str_replace('[iterar_tabla]', $tabla, $body);
                }

                // LISTA

                if (str_contains($body, '[iterar_lista]') && $registros->isNotEmpty()) {

                    $lista = '<ul>';

                    foreach ($registros as $registro) {
                        $lista .= '<li>' . implode(' - ', $registro) . '</li>';
                    }

                    $lista .= '</ul>';

                    $body = str_replace('[iterar_lista]', $lista, $body);
                }

                // PARRAFOS

                if (str_contains($body, '[iterar_parrafos]') && $registros->isNotEmpty()) {

                    $parrafos = '';

                    foreach ($registros as $registro) {
                        $parrafos .= '<p>' . implode(' | ', $registro) . '</p>';
                    }

                    $body = str_replace('[iterar_parrafos]', $parrafos, $body);
                }
            }

            // INYECTAR EN PLANTILLA

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
                        mb_convert_encoding(
                            '<div>' . $body . '</div>',
                            'HTML-ENTITIES',
                            'UTF-8'
                        )
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


            $mailer->send(
                $userDestino->email,
                new \App\Mail\CorreoDinamico(
                    $subject,
                    $body,
                    $userDestino
                )
            );

            $audit['detalle'][] = [
                'tac' => 'TAC-002',
                'usuario_id' => $userDestino->id,
                'usuario_nombre' => $userDestino->name ?? null,
                'usuario_email' => $userDestino->email,
                'asunto' => $subject,
                'plantilla_id' => $templateId,
                'modo' => $esMultiple ? 'multiple' : 'individual',
                'estado' => 'enviado',
            ];
        }

        $audit['mensaje'] =
            'Se enviaron ' .
            count($audit['detalle']) .
            ' correos electrónicos';

        return [
            'success' => true,
            'audit' => $audit
        ];
    }

    private function EjecutarModificarCampo($respuestas, $esMultiple, $parametros, $action, $condicionesIgual)
    {

        $audit = [
            'detalle' => []
        ];

        $respuestasCollection = $esMultiple
            ? $respuestas
            : collect([$respuestas->first() ?? $respuestas]);

        $CampoDestinoId = $parametros['campo_ref_id'] ?? null;
        $CampoDestino = CamposForm::find($CampoDestinoId);

        if (!$CampoDestino) {
            return [
                'success' => false,
                'audit' => $audit
            ];
        }

        $tipoValor = $parametros['tipo_valor'] ?? null;
        $valorRaw = $parametros['valor'] ?? null;

        $campoOrigenId = null;

        if ($tipoValor == 'campo') {
            $campoOrigenId = $valorRaw;
        }

        foreach ($respuestasCollection as $respuestaOrigen) {

            $valor = null;

            $filasSeleccionadas = $respuestaOrigen->filasSeleccionadas ?? [];
            $filasOriginales = $respuestaOrigen->filasOriginales ?? [];

            if ($tipoValor == 'campo') {

                $resultadoOrigen = $this->GetResultadoByCampoOrigen(
                    $filasSeleccionadas,
                    $campoOrigenId
                );

                if (collect($resultadoOrigen)->filter()->isEmpty()) {

                    $resultadoOrigen = $this->GetResultadoByCampoOrigen(
                        $filasSeleccionadas,
                        $campoOrigenId,
                        $parametros['form_origen_id']
                    );
                }

                $valor = $resultadoOrigen['valor'];

            } else {
                $valor = $valorRaw;
            }

            $resultadoDestino = $this->GetResultadoByCampoOrigen(
                $filasSeleccionadas,
                $CampoDestinoId,
                $parametros['form_ref_id'],
                $valor
            );

            if (collect($resultadoDestino)->filter()->isEmpty()) {
                //SI NO ENCUENTRA RESPUESTA DESTINO CON LAS FILAS SELECCIONADAS, 
                //INTENTA OBTENER RESPUESTAS QUE CUMPLAN CON LAS CONDICIONES DE IGUALDAD PARA MODIFICAR EL CAMPO EN ESAS RESPUESTAS
                $respuestaIds = $this->GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros);
                $respuestaIds = array_values($respuestaIds);

            }

            if (!collect($resultadoDestino)->filter()->isEmpty() || count($respuestaIds ?? []) > 0) {

                $respuestaDestino = RespuestasCampo::firstWhere([
                    'respuesta_id' => $resultadoDestino['respuesta_id'],
                    'cf_id' => $CampoDestinoId,
                ]);

                if ($respuestaDestino == null) {

                    $respuestaDestino = RespuestasCampo::whereIn('respuesta_id', $respuestaIds)
                        ->where('cf_id', $CampoDestinoId)
                        ->first();
                }

                $valorAnterior = $respuestaDestino->valor;

                switch ($parametros['operacion']) {

                    case 'OPC-001':

                        $respuestaDestino->valor += (float) $valor;

                        break;

                    case 'OPC-002':
                        $respuestaDestino->valor -= (float) $valor;
                        break;

                    case 'OPC-003':
                        $respuestaDestino->valor *= (float) $valor;
                        break;

                    case 'OPC-004':
                        if ((float) $valor !== 0.0) {
                            $respuestaDestino->valor /= (float) $valor;
                        }
                        break;

                    case 'OPC-005':
                    case 'OPC-006':
                        $respuestaDestino->valor = $valor;
                        break;

                    case 'OPC-007':
                        $campoOrigen = RespuestasCampo::find($valor);
                        $respuestaDestino->valor = $campoOrigen?->valor;
                        break;

                    case 'OPC-008':
                        $respuestaDestino->valor = ($respuestaDestino->valor ?? '') . $valor;
                        break;

                    case 'OPC-009':
                        $respuestaDestino->valor = null;
                        break;

                    case 'OPC-010':
                        $respuestaDestino->valor = \Carbon\Carbon::parse($respuestaDestino->valor)
                            ->addDays((int) $valor)
                            ->format('Y-m-d');
                        break;

                    case 'OPC-011':
                        $respuestaDestino->valor = \Carbon\Carbon::parse($respuestaDestino->valor)
                            ->subDays((int) $valor)
                            ->format('Y-m-d');
                        break;

                    case 'OPC-012':
                        $result = $this->OperacionesDelta(
                            $filasSeleccionadas,
                            $filasOriginales,
                            $campoOrigenId,
                            $parametros,
                            $respuestaDestino,
                            $valor
                        );

                        $respuestaDestino->valor = $result;
                        break;
                }

                $respuestaDestino->save();

                $audit['detalle'][] = [
                    'tac' => 'TAC-001',
                    'campo_destino_id' => $CampoDestino->id,
                    'campo_destino_nombre' => $CampoDestino->nombre,
                    'respuesta_id' => $respuestaDestino->respuesta_id,
                    'respuesta_campo_id' => $respuestaDestino->id,
                    'operacion_codigo' => $parametros['operacion'],
                    'operacion_nombre' => $action->OperacionCatalogo,
                    'valor_aplicado' => $valor,
                    'valor_anterior' => $valorAnterior,
                    'valor_nuevo' => $respuestaDestino->valor,
                    'modo' => $esMultiple ? 'multiple' : 'individual',
                ];
            }
        }

        $audit['mensaje'] =
            "Se actualizaron {$CampoDestino->nombre} mediante la operación {$action->OperacionCatalogo}";

        return [
            'success' => true,
            'audit' => $audit
        ];
    }
    private function OperacionesDelta($filasSeleccionadas, $filasOriginales, $campoOrigenId, $parametros, $respuestasDestino, $valor)
    {
        $resultado = $this->GetResultadoByCampoOrigen($filasSeleccionadas, $campoOrigenId, $parametros['form_origen_id']);

        $campoOrigen = CamposForm::find($resultado['campo_id']);

        $valorId = $filasOriginales[$campoOrigen->id] ?? null;


        $valoranterior = preg_replace('/\[\d+\]\s*/', '', $valorId);


        $result = $respuestasDestino->valor;
        $valornuevo = $valor;


        if ($parametros['operacion_rev'] == '1') {

            $result += ($valornuevo - $valoranterior);


        } else {

            $result += ($valoranterior - $valornuevo);

        }
        return $result;
    }

    public function EjecutarReglaLogica($reglas, array $respuestas, string $evento, $usuario, $url)
    {
        $user = User::find($usuario);

        $respuestasModelos = collect();

        foreach ($respuestas as $item) {

            $respuesta = RespuestasForm::find($item['respuesta_id']);

            if ($respuesta) {
                $respuesta->filasSeleccionadas = $item['filas'];
                $respuesta->filasOriginales = $item['filas_originales'] ?? null;
                $respuestasModelos->push($respuesta);
            }
        }

        $resultado = $this->ejecutarLogica(
            $reglas,
            $respuestasModelos,
            $evento,
            $usuario
        );
        if ($user && !empty($resultado['acciones_ejecutadas'])) {

            foreach ($resultado['acciones_ejecutadas'] as $accion) {

                $tipo_accion = $this->CatalogoRepository
                    ->getNombreCatalogo($accion['tipo_accion']);

                $detalle = [
                    'accion_id' => $accion['accion_id'] ?? null,
                    'tipo_accion' => $tipo_accion ?? null,
                    'mensaje' => $accion['mensaje'] ?? '',
                    'detalle' => $accion['detalle'] ?? [],
                    'errores' => $accion['errores'] ?? [],
                    'ok' => $accion['ok'] ?? false,
                ];

                $auditoria = AuditoriaAccion::create([
                    'action_id' => $accion['accion_id'],
                    'tipo_accion' => $tipo_accion,
                    'usuario_id' => $usuario,
                    'estado' => $accion['ok'] ? 'success' : 'error',
                    'mensaje' => $accion['mensaje'],
                    'detalle' => $accion,
                    'errores' => $accion['errores'],
                ]);

                $ruta = $url . '/formulario/logica/detalle/' . $auditoria->id;

                $user->notify(
                    new LogicaFormularioFinalizada($detalle, $ruta)
                );
            }
        }

    }

    public function EjecutarAcciones($agrupadas, $evento)
    {


        foreach ($agrupadas as $formId => $respuestasForm) {


            $reglas = FormLogicRule::where('form_id', $formId)
                ->where('evento', $evento)
                ->where('activo', true)
                ->with([
                    'actions' => function ($q) {
                        $q->with('conditions');
                    }
                ])
                ->get();


            // Separar reglas síncronas y segundo plano
            $reglasSync = $reglas->filter(function ($regla) {
                return !$regla->segundo_plano;
            });

            $reglasQueue = $reglas->filter(function ($regla) {
                return $regla->segundo_plano;
            });


            // Ejecutar sincronamente
            if ($reglasSync->isNotEmpty()) {

                $this->EjecutarReglaLogica(
                    $reglasSync,
                    $respuestasForm->toArray(),
                    $evento,
                    auth()->id(),
                    env('APP_URL')
                );
            }

            // Ejecutar en cola
            if ($reglasQueue->isNotEmpty()) {

                EjecutarLogicaFormulario::dispatch(
                    $reglasQueue->values(),
                    $respuestasForm->toArray(),
                    $evento,
                    auth()->id(),
                    env('APP_URL')
                );
            }
        }

    }

    public function LogicaEliminarRespuesta($respuesta)
    {
        $form = $respuesta->form_id;
        $evento = 'on_delete';

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

        $errores = array_filter($this->ValidarLogica($respuesta, $filas, $evento), fn($msg) => !empty(trim($msg)));

        if (!empty($errores)) {
            return [
                'success' => false,
                'errores' => array_values($errores)
            ];
        }

        $this->EjecutarAcciones($agrupadas, $evento);

        $this->FormularioRepository->EliminarArchivos($respuesta);

        $respuesta->camposRespuestas()->delete();

        $respuesta->delete();

        return [
            'success' => true,
            'mensaje' => configForm($form, 'messages.success_delete')
        ];
    }
}
