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
use App\Models\FormLogicExecution;
use App\Models\FormularioAsociacion;

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

    public function CrearRegla($request, $modulo_id)
    {

        $acciones = json_decode($request->acciones_json, true);

        $parametros = null;
        $segundo_plano = $request->has('segundo_plano');
        if ($request->evento === 'scheduled') {
            $parametros = [
                'programacion' => $request->programacion,
            ];
            $segundo_plano = 1;
        }

        $rule = FormLogicRule::create([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id_disparador,
            'modulo_id' => $modulo_id,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'segundo_plano' => $segundo_plano,
            'parametros' => $parametros
        ]);


        $this->guardarAccionesYCondiciones($rule, $acciones);

        return $rule;
    }

    public function EditarRegla($request, $form_logic)
    {
        $acciones = json_decode($request->acciones_json, true);

        $form_logic = FormLogicRule::findOrFail($form_logic);

        $parametros = null;
        $segundo_plano = $request->has('segundo_plano');

        if ($request->evento === 'scheduled') {
            $parametros = [
                'programacion' => $request->programacion,
            ];

            // Las tareas programadas siempre se ejecutan en segundo plano
            $segundo_plano = 1;
        }

        $form_logic->update([
            'nombre' => $request->nombre,
            'form_id' => $request->formulario_id_disparador,
            'evento' => $request->evento,
            'activo' => $request->has('activo'),
            'segundo_plano' => $segundo_plano,
            'parametros' => $parametros,
        ]);

        // Eliminar acciones existentes y sus condiciones
        $form_logic->actions()->delete();
        $this->guardarAccionesYCondiciones($form_logic, $acciones);

        return $form_logic;
    }
    // Función para guardar acciones y condiciones
    protected function guardarAccionesYCondiciones(FormLogicRule $rule, array $acciones)
    {
        foreach ($acciones as $actionData) {
            // Preparamos los parámetros extra según el tipo de acción
            $parametrosExtra = [];

            switch ($actionData['tipo_accion_id']) {
                case 'TAC-001': // modificar campos

                    $parametrosExtra = [

                        // Formulario donde se ejecuta la acción
                        'form_origen_id' => $actionData['form_origen_id'] ?? null,


                        // Formulario donde se modifican datos
                        'form_ref_id' => $actionData['form_ref_id'] ?? null,


                        // Condiciones para ejecutar
                        'condiciones' => $actionData['condiciones'] ?? [],


                        // Campos que serán modificados
                        'asignaciones' => $actionData['asignaciones'] ?? [],


                        // Datos descriptivos
                        'tipo_accion_text' => $actionData['tipo_accion_text'] ?? '',

                        'form_ref_text' => $actionData['form_ref_text'] ?? '',

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

        if (array_key_exists($nombreCampo, $filasSeleccionadas) && !is_array($filasSeleccionadas[$nombreCampo])) {

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
        $usuario,
        $esCascada = false
    ): array {
        $esMultiple = $respuestas instanceof \Illuminate\Support\Collection;

        $respuestas = $esMultiple ? collect($respuestas) : collect($respuestas ? [$respuestas] : []);

        $respuesta = $respuestas->first();

        $form = Formulario::find($respuesta?->form_id);

        $resultados = [
            'ok' => true,
            'evento' => $evento,
            'form_id' => $respuesta->form_id ?? null,
            'respuesta_id' => $respuesta->id ?? null,
            'acciones_ejecutadas' => [],
            'errores' => [],
            'mensaje' => ''
        ];


        foreach ($reglas as $regla) {
            foreach ($regla->actions as $action) {
                //if ($action->id == 91) { //////SOLO PARA VALIDAR UNA ACCION/////////////////
                $resultadoAccion = $this->ejecutarAccion(
                    $regla,
                    $respuestas,
                    $action,
                    $usuario,
                    $form->config['registro_multiple'] ?? false,
                    $esCascada
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
                //}
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
            ->with(['actions'])->get();

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

            if (isset($condicion['tipo_condicion']) && $condicion['tipo_condicion'] === 'campo_relacionado' && $valorEvaluar['valor'] === null) {
                //CASO PARA CAMPO RELACIONADO, PARA DONDE SE PIDE VALIDAR CON EL VALOR RELACIONADO DEL FORMULARIO PRINCIPAL
                $valorEvaluar = $this->resolverValorEvaluar($condicion['campo_condicion_origen'], $filasSeleccionadas, $condicion['formulario_relacion_origen']);

            }

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
    private function resolverFormulaAsignacion(
        $formula,
        $filasSeleccionadas,
        $respuestaDestino = null,
        $filasOriginales = [],
        $parametros = []
    ) {
        $tokens = [];

        $tieneFuncion = false;
        $funcionActual = null;

        /*
        |--------------------------------------------------------------------------
        | Detectar funciones
        |--------------------------------------------------------------------------
        */

        foreach ($formula as $elemento) {

            if (($elemento['tipo'] ?? null) === 'funcion') {

                $tieneFuncion = true;

                $funcionActual = $elemento['nombre'] ?? null;

                break;
            }

        }



        /*
        |--------------------------------------------------------------------------
        | Procesar funciones
        |--------------------------------------------------------------------------
        */

        if ($tieneFuncion) {


            switch ($funcionActual) {


                case 'DELTA':
                    return $this->resolverFuncionDelta(
                        $formula,
                        $filasSeleccionadas,
                        $filasOriginales,
                        $respuestaDestino
                    );


                default:

                    return null;

            }

        }



        /*
        |--------------------------------------------------------------------------
        | Procesar expresión normal
        |
        | Ejemplo:
        |
        | campo + campo - campo
        |
        |--------------------------------------------------------------------------
        */

        foreach ($formula as $elemento) {


            switch ($elemento['tipo'] ?? null) {



                /*
                |--------------------------------------------------------------------------
                | Campo
                |--------------------------------------------------------------------------
                */

                case 'campo':


                    $contexto = $elemento['contexto'] ?? 'origen';



                    /*
                    |--------------------------------------------------------------------------
                    | Campo formulario origen
                    |--------------------------------------------------------------------------
                    */

                    if ($contexto === 'origen') {


                        $resultado = $this->GetResultadoByCampoOrigen(
                            $filasSeleccionadas,
                            $elemento['campo_id'],
                            $elemento['form'] ?? null
                        );



                        if (
                            !is_array($resultado) ||
                            !isset($resultado['valor'])
                        ) {

                            return null;

                        }



                        $tokens[] = $resultado['valor'];



                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Campo formulario destino
                    |--------------------------------------------------------------------------
                    */ else {



                        if (!$respuestaDestino) {
                            return null;
                        }



                        $campoDestino = RespuestasCampo::where([
                            'respuesta_id' => $respuestaDestino['respuesta_id'],
                            'cf_id' => $elemento['campo_id']
                        ])
                            ->first();




                        if (!$campoDestino) {
                            return null;
                        }

                        $tokens[] = $campoDestino->valor ?? 0;


                    }



                    break;




                /*
                |--------------------------------------------------------------------------
                | Operadores
                |--------------------------------------------------------------------------
                */

                case 'operador':

                    $tokens[] = $elemento['valor'];

                    break;




                /*
                |--------------------------------------------------------------------------
                | Valores fijos
                |--------------------------------------------------------------------------
                */

                case 'valor':

                    $tokens[] = $elemento['valor'];

                    break;



            }

        }



        return $this->evaluarExpresionLineal($tokens);

    }
    private function evaluarExpresionLineal($tokens)
    {

        if (empty($tokens)) {
            return null;
        }


        $resultado = array_shift($tokens);



        while (count($tokens) >= 2) {


            $operador = array_shift($tokens);

            $valor = array_shift($tokens);



            switch ($operador) {


                case '+':
                    $resultado += $valor;
                    break;


                case '-':
                    $resultado -= $valor;
                    break;


                case '*':
                    $resultado *= $valor;
                    break;


                case '/':

                    if ($valor == 0) {
                        return null;
                    }

                    $resultado /= $valor;

                    break;


                default:
                    return null;

            }

        }


        return $resultado;

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

                $condicionesIgual = [];
                $otrasCondiciones = [];

                foreach ($parametros['condiciones'] ?? [] as $condicion) {

                    if (($condicion['operador'] ?? '=') === '=') {

                        if (
                            !isset($condicion['tipo_condicion']) ||
                            $condicion['tipo_condicion'] !== 'form_valor'
                        ) {

                            $condicionesIgual[] = $condicion;

                        } else {

                            $otrasCondiciones[] = $condicion;
                        }

                    } else {

                        $otrasCondiciones[] = $condicion;
                    }
                }



                $mensaje = $this->ValidarCondicionesIgualdad(
                    $condicionesIgual,
                    $filasSeleccionadas,
                    $parametros,
                    $respuestaOrigen
                );


                if ($mensaje != '') {
                    break;
                }


                foreach ($parametros['asignaciones'] ?? [] as $asignacion) {


                    $campoDestinoId =
                        $asignacion['destino']['campo_id'] ?? null;


                    if (!$campoDestinoId) {

                        $mensaje = "No existe campo destino en asignación de acción {$action->id}";
                        break;
                    }


                    $campoDestino = CamposForm::find($campoDestinoId);


                    if (!$campoDestino) {

                        $mensaje = "No existe campo destino ID {$campoDestinoId} para {$accionNombre}, acción {$action->id}";
                        break;
                    }



                    $valor_principal = null;


                    $modo = $asignacion['modo'] ?? null;



                    switch ($modo) {


                        case 'valor':

                            $valor_principal = $asignacion['valor'] ?? null;


                            if ($valor_principal === null || $valor_principal === '') {

                                $mensaje = "Valor fijo no definido para campo {$campoDestinoId}";
                                break 2;
                            }


                            break;

                        case 'campo':

                            $campoOrigenId = $asignacion['origen']['campo_id'] ?? null;


                            if (!$campoOrigenId) {

                                $mensaje = "Campo origen no definido para asignación {$action->id}";
                                break 2;
                            }



                            $resultado = $this->GetResultadoByCampoOrigen(
                                $filasSeleccionadas,
                                $campoOrigenId,
                                $asignacion['origen']['form'] ?? null
                            );


                            if (
                                !is_array($resultado) ||
                                blank($resultado['valor'] ?? null)
                            ) {

                                $mensaje = "No se encontró valor para campo origen {$campoOrigenId}";
                                break 2;
                            }


                            $valor_principal = $resultado['valor'];


                            break;


                        case 'calculo':
                        case 'funcion':

                            $campoDestino = $asignacion['destino']['campo_id'] ?? null;


                            if (!$campoDestino) {

                                $mensaje = "Campo destino no definido para asignación {$action->id}";
                                break 2;
                            }

                            $resultado = $this->GetResultadoByCampoOrigen(
                                $filasSeleccionadas,
                                $campoDestino,
                                $asignacion['destino']['form'] ?? null
                            );

                            if (collect($resultado)->filter()->isEmpty()) {

                                //SI NO ENCUENTRA RESPUESTA DESTINO CON LAS FILAS SELECCIONADAS, 
                                //INTENTA OBTENER RESPUESTAS QUE CUMPLAN CON LAS CONDICIONES DE IGUALDAD PARA MODIFICAR EL CAMPO EN ESAS RESPUESTAS
                                $respuestaIds = $this->GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros);
                                $respuestaIds = array_values($respuestaIds);

                                $respuestaDestino = RespuestasCampo::whereIn('respuesta_id', $respuestaIds)
                                    ->where('cf_id', $campoDestino)
                                    ->first();

                                $resultado = [
                                    'formulario_id' => $respuestaDestino->respuesta->form_id ?? null,
                                    'respuesta_id' => $respuestaDestino->respuesta_id ?? null,
                                    'campo_id' => $campoDestino,
                                    'valor' => $respuestaDestino->valor ?? null,
                                    'from_relation' => false
                                ];
                            }

                            /*
                                                        $valor_principal = $this->resolverFormulaAsignacion(
                                                            $asignacion['formula'] ?? [],
                                                            $filasSeleccionadas,
                                                            $resultado
                                                        );


                                                        if ($valor_principal === null) {

                                                            $mensaje = "No se pudo resolver cálculo para campo {$campoDestinoId}";
                                                            break 2;
                                                        }*/


                            break;



                        default:

                            $mensaje = "Modo de asignación inválido: {$modo}";
                            break 2;

                    }

                    $mensaje = $this->ValidarOtrasCondiciones(
                        $otrasCondiciones,
                        $filasSeleccionadas,
                        $parametros,
                        $respuestaOrigen,
                        $valor_principal
                    );


                    if ($mensaje != '') {
                        break;
                    }

                    Log::info("TAC-001 preparado | Campo {$campoDestinoId} = {$valor_principal}");

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
    public function ejecutarAccion($regla, $respuestas, $action, $usuario, $esMultiple, $esCascada = false): array
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

                    $resultado = $this->EjecutarModificarCampo($respuestas, $esMultiple, $parametros, $action, $condicionesIgual, $regla);

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

                    if (!$esCascada) {


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


                        $respuestaIds = $this->GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros);

                        $respuestaIds = array_values($respuestaIds);




                        if (!collect($respuestaIds)->filter()->isEmpty()) {

                            $respuestas = RespuestasForm::whereIn('id', $respuestaIds)->get();

                            $errores = [];
                            $visitados = [];

                            foreach ($respuestas as $respuesta) {

                                $resultadoEliminar = $this->LogicaEliminarRespuesta(
                                    'on_delete',
                                    $respuesta,
                                    false,
                                    $visitados
                                );

                                Log::info('Resultado eliminar', ['resultado' => $resultadoEliminar]);

                                if (!$resultadoEliminar['success']) {
                                    $errores = array_merge(
                                        $errores,
                                        $resultadoEliminar['errores'] ?? []
                                    );
                                }
                            }

                            $errores = array_values(array_unique($errores));

                            $resultado = [
                                'success' => empty($errores),
                                'audit' => [
                                    'mensaje' => implode(' | ', $errores),
                                    'detalle' => [],
                                ]
                            ];
                        } else {
                            $resultado = [
                                'success' => false,
                                'audit' => [
                                    'mensaje' => 'No existen respuestas para ejecutar la acción de eliminar registros',
                                    'detalle' => [],
                                ]
                            ];
                        }
                    } else {

                        $resultado = [
                            'success' => true,
                            'audit' => [
                                'mensaje' => 'Eliminación omitida por cascada',
                                'detalle' => [],
                            ]
                        ];

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
    public function esRegistroPadreDe(RespuestasForm $respuestaActual, RespuestasForm $respuestaObjetivo)
    {
        $asociaciones = FormularioAsociacion::all();

        foreach ($asociaciones as $asociacion) {

            foreach ($asociacion->config ?? [] as $regla) {

                if (
                    empty($regla['relacion_multiple']) ||
                    ($regla['modo'] ?? null) !== 'asignacion'
                ) {
                    continue;
                }


                $campoRelacion = collect($regla['formula'] ?? [])
                    ->first(
                        fn($item) =>
                        ($item['tipo'] ?? null) === 'campo'
                    );


                if (!$campoRelacion) {
                    continue;
                }


                $formPadre = (int) $campoRelacion['form'];
                $campoPadre = (int) $campoRelacion['campo_id'];

                $formHijo = (int) $regla['destino']['form'];
                $campoHijo = (int) $regla['destino']['campo_id'];


                // La relación es:
                // padre -> hijo

                if (
                    $respuestaActual->form_id == $formHijo &&
                    $respuestaObjetivo->form_id == $formPadre
                ) {

                    $valorPadre = $respuestaObjetivo
                        ->camposRespuestas
                        ->firstWhere('cf_id', $campoPadre)
                            ?->valor;


                    $valorHijo = $respuestaActual
                        ->camposRespuestas
                        ->firstWhere('cf_id', $campoHijo)
                            ?->valor;


                    if ($valorPadre == $valorHijo) {
                        return true;
                    }
                }
            }
        }


        return false;
    }
    private function GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros)
    {
        foreach ($condicionesIgual as $condicion) {

            if (isset($condicion['tipo_condicion']) && $condicion['tipo_condicion'] === 'form_valor') {
                continue;
            }

            $valorEvaluar = $this->resolverValorEvaluar(
                $condicion['campo_condicion_origen'],
                $filasSeleccionadas,
                $parametros['form_origen_id']
            );

            if (isset($condicion['tipo_condicion']) && $condicion['tipo_condicion'] === 'campo_relacionado' && $valorEvaluar['valor'] === null) {
                //CASO PARA CAMPO RELACIONADO, PARA DONDE SE PIDE VALIDAR CON EL VALOR RELACIONADO DEL FORMULARIO PRINCIPAL
                $valorEvaluar = $this->resolverValorEvaluar($condicion['campo_condicion_origen'], $filasSeleccionadas, $condicion['formulario_relacion_origen']);

            }

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

    private function EjecutarModificarCampo(
        $respuestas,
        $esMultiple,
        $parametros,
        $action,
        $condicionesIgual,
        $regla
    ) {

        $audit = ['detalle' => []];


        $respuestasCollection = $esMultiple
            ? $respuestas
            : collect([$respuestas->first() ?? $respuestas]);



        /*
        |--------------------------------------------------------------------------
        | NUEVA ESTRUCTURA:
        | recorrer todas las asignaciones
        |--------------------------------------------------------------------------
        */

        $asignaciones = $parametros['asignaciones'] ?? [];


        if (empty($asignaciones)) {

            return [
                'success' => false,
                'audit' => $audit
            ];
        }



        /*
        |--------------------------------------------------------------------------
        | EVENTO NORMAL
        |--------------------------------------------------------------------------
        */

        if ($regla->evento != 'scheduled') {


            foreach ($respuestasCollection as $respuestaOrigen) {


                $filasSeleccionadas = $respuestaOrigen->filasSeleccionadas ?? [];

                $filasOriginales = $respuestaOrigen->filasOriginales ?? [];


                foreach ($asignaciones as $asignacion) {



                    /*
                    |--------------------------------------------------------------------------
                    | Campo destino
                    |--------------------------------------------------------------------------
                    */

                    $campoDestinoId = $asignacion['destino']['campo_id'] ?? null;
                    $campoDestinoText = $asignacion['destino']['nombre'] ?? null;


                    $formDestinoId = $asignacion['destino']['form'] ?? $parametros['form_ref_id'];




                    /*
                    |--------------------------------------------------------------------------
                    | Resolver formula
                    |--------------------------------------------------------------------------
                    */

                    $resultado = $this->GetResultadoByCampoOrigen(
                        $filasSeleccionadas,
                        $campoDestinoId,
                        $asignacion['destino']['form'] ?? null
                    );


                    if (collect($resultado)->filter()->isEmpty()) {

                        //SI NO ENCUENTRA RESPUESTA DESTINO CON LAS FILAS SELECCIONADAS, 
                        //INTENTA OBTENER RESPUESTAS QUE CUMPLAN CON LAS CONDICIONES DE IGUALDAD PARA MODIFICAR EL CAMPO EN ESAS RESPUESTAS
                        $respuestaIds = $this->GetRespuestaIdsByCondicion($condicionesIgual, $filasSeleccionadas, $parametros);
                        $respuestaIds = array_values($respuestaIds);

                        $respuestaDestino = RespuestasCampo::whereIn('respuesta_id', $respuestaIds)
                            ->where('cf_id', $campoDestinoId)
                            ->first();

                        $resultado = [
                            'formulario_id' => $respuestaDestino->respuesta->form_id ?? null,
                            'respuesta_id' => $respuestaDestino->respuesta_id ?? null,
                            'campo_id' => $campoDestinoId,
                            'valor' => $respuestaDestino->valor ?? null,
                            'from_relation' => false
                        ];
                    }




                    $valor = $this->resolverFormulaAsignacion(
                        $asignacion['formula'] ?? [],
                        $filasSeleccionadas,
                        $resultado,
                        $filasOriginales
                    );

                    if ($valor === null) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Buscar registro destino
                    |--------------------------------------------------------------------------
                    */


                    $resultadoDestino = $this->GetResultadoByCampoOrigen(
                        $filasSeleccionadas,
                        $campoDestinoId,
                        $formDestinoId,
                        $valor
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Si no encuentra por relación,
                    | buscar por condiciones iguales
                    |--------------------------------------------------------------------------
                    */


                    $respuestaIds = [];


                    if (collect($resultadoDestino)->filter()->isEmpty()) {


                        $respuestaIds =
                            $this->GetRespuestaIdsByCondicion(
                                $condicionesIgual,
                                $filasSeleccionadas,
                                $parametros
                            );


                        $respuestaIds = array_values($respuestaIds);

                    }




                    if (!collect($resultadoDestino)->filter()->isEmpty() || count($respuestaIds) > 0) {



                        $respuestaDestino = RespuestasCampo::firstWhere([
                            'respuesta_id' => $resultadoDestino['respuesta_id'] ?? null,
                            'cf_id' => $campoDestinoId,
                        ]);





                        if (!$respuestaDestino) {


                            $respuestaDestino =
                                RespuestasCampo::whereIn(
                                    'respuesta_id',
                                    $respuestaIds
                                )
                                    ->where('cf_id', $campoDestinoId)
                                    ->first();

                        }



                        if (!$respuestaDestino) {
                            continue;
                        }



                        $valorAnterior = $respuestaDestino->valor;




                        /*
                        |--------------------------------------------------------------------------
                        | Actualizar campo
                        |--------------------------------------------------------------------------
                        */


                        $respuestaDestino->valor = $valor;

                        $respuestaDestino->save();


                        $audit['detalle'][] = [

                            'Tipo' => 'Normal',

                            'tac' => 'TAC-001',

                            'campo_destino_id' => $campoDestinoId,

                            'campo_destino_nombre' => $campoDestinoText,

                            'respuesta_id' => $respuestaDestino->respuesta_id,

                            'respuesta_campo_id' => $respuestaDestino->id,

                            'valor_aplicado' => $valor,

                            'valor_anterior' => $valorAnterior,

                            'valor_nuevo' => $respuestaDestino->valor,

                            'modo' => $esMultiple ? 'multiple' : 'individual',

                        ];


                    }


                }

            }



        }



        /*
        |--------------------------------------------------------------------------
        | PROGRAMADO
        |--------------------------------------------------------------------------
        */ else {


            foreach ($asignaciones as $asignacion) {


                $campoDestinoId =
                    $asignacion['destino']['campo_id'];


                $campoDestino =
                    CamposForm::find($campoDestinoId);



                if (!$campoDestino) {
                    continue;
                }



                $respuestasDestino =
                    RespuestasForm::with('camposRespuestas')
                        ->where('form_id', $parametros['form_ref_id'])
                        ->get();




                foreach ($respuestasDestino as $respuestaForm) {


                    $respuestaDestino =
                        $respuestaForm->camposRespuestas
                            ->firstWhere(
                                'cf_id',
                                $campoDestinoId
                            );



                    if (!$respuestaDestino) {
                        continue;
                    }



                    $valor = $this->resolverFormulaAsignacion(
                        $asignacion['formula'] ?? [],
                        [],
                        $parametros
                    );



                    $valorAnterior = $respuestaDestino->valor;


                    $respuestaDestino->valor = $valor;
                    $respuestaDestino->save();



                    $audit['detalle'][] = [

                        'Tipo' => 'Scheduled',

                        'tac' => 'TAC-001',

                        'campo_destino_id' => $campoDestino->id,

                        'campo_destino_nombre' => $campoDestino->nombre,

                        'respuesta_id' => $respuestaDestino->respuesta_id,

                        'valor_aplicado' => $valor,

                        'valor_anterior' => $valorAnterior,

                        'valor_nuevo' => $respuestaDestino->valor,

                    ];


                }


            }


        }



        $audit['mensaje'] =
            "Se ejecutaron las asignaciones de {$action->tipo_accion_catalogo}";



        return [
            'success' => true,
            'audit' => $audit
        ];

    }
    private function resolverFuncionDelta($formula, $filasSeleccionadas, $filasOriginales, $respuestaDestino)
    {
        $campoOrigen = null;
        $inverso = false;


        //LEER FORMULA

        foreach ($formula as $elemento) {

            if (($elemento['tipo'] ?? null) === 'campo') {
                $campoOrigen = $elemento;
            }

            if (
                ($elemento['tipo'] ?? null) === 'funcion' &&
                ($elemento['nombre'] ?? null) === 'INV'
            ) {
                $inverso = true;
            }
        }

        //VALIDAR CAMPO

        if (!$campoOrigen || empty($campoOrigen['campo_id'])) {
            return null;
        }

        $campo = CamposForm::find($campoOrigen['campo_id']);

        if (!$campo) {
            return null;
        }

        //VALOR NUEVO

        $resultado = $this->GetResultadoByCampoOrigen(
            $filasSeleccionadas,
            $campoOrigen['campo_id'],
            $campoOrigen['form'] ?? null
        );

        if (!is_array($resultado) || !array_key_exists('valor', $resultado)) {
            return null;
        }

        $valorNuevo = $resultado['valor'];
        // VALOR ANTERIOR

        $valorAnterior = $filasOriginales[$campo->id] ?? null;

        // Eliminar prefijos como "[123] "
        $valorAnterior = preg_replace('/\[\d+\]\s*/', '', (string) $valorAnterior);

        //VALOR ACTUAL DEL DESTINO

        $actual = (float) ($respuestaDestino['valor'] ?? 0);

        //NORMALIZAR VALORES

        $valorNuevo = is_numeric($valorNuevo)
            ? (float) $valorNuevo
            : 0;

        $valorAnterior = is_numeric($valorAnterior)
            ? (float) $valorAnterior
            : 0;

        // CALCULAR DELTA

        if ($inverso) {
            return $actual + ($valorNuevo - $valorAnterior);
        }

        return $actual + ($valorAnterior - $valorNuevo);
    }
    public function EjecutarReglaLogica($reglas, array $respuestas, string $evento, $usuario, $url, $esCascada = false)
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
            $usuario,
            $esCascada
        );


        if (!empty($resultado['acciones_ejecutadas'])) {

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
                if ($user) {

                    $user->notify(new LogicaFormularioFinalizada($detalle, $ruta));
                } else {
                    //SI NO HAY USUARIO SE ENVIA A TODOS LOS ADMINISTRADORES DEL SISTEMA
                    $admins = User::role('admin')->get();

                    foreach ($admins as $admin) {
                        $admin->notify(new LogicaFormularioFinalizada($detalle, $ruta));
                    }
                }
            }
        }

    }

    public function EjecutarAcciones($agrupadas, $evento, $esCascada = false)
    {


        foreach ($agrupadas as $formId => $respuestasForm) {


            $reglas = FormLogicRule::where('form_id', $formId)
                ->where('evento', $evento)
                ->where('activo', true)
                ->with('actions')
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
                    env('APP_URL'),
                    $esCascada
                );
            }

            // Ejecutar en cola
            if ($reglasQueue->isNotEmpty()) {

                EjecutarLogicaFormulario::dispatch(
                    $reglasQueue->values(),
                    $respuestasForm->toArray(),
                    $evento,
                    auth()->id(),
                    env('APP_URL'),
                    $esCascada
                );
            }
        }

    }
    public function ejecutarTareasProgramadas()
    {
        Log::info('========================================');
        Log::info('INICIO EJECUCIÓN DE TAREAS PROGRAMADAS');
        Log::info('Fecha/Hora: ' . now());

        $reglas = FormLogicRule::where('evento', 'scheduled')
            ->where('activo', true)
            ->with([
                'actions',
                'ejecuciones' => function ($q) {
                    $q->latest('inicio')->limit(1);
                }
            ])
            ->get();

        Log::info("Reglas encontradas: {$reglas->count()}");

        if ($reglas->isEmpty()) {
            Log::info('No existen tareas programadas activas.');
            return;
        }

        foreach ($reglas as $regla) {

            Log::info("----------------------------------------");
            Log::info("Evaluando regla #{$regla->id}");
            Log::info("Nombre: {$regla->nombre}");

            try {

                $ejecutar = $this->debeEjecutarseAhora($regla);

                if (!$ejecutar) {

                    Log::info("La regla {$regla->id} NO debe ejecutarse en este momento.");

                    continue;
                }

                Log::info("La regla {$regla->id} será ejecutada.");

                $this->ejecutarTarea($regla);

                Log::info("La regla {$regla->id} finalizó correctamente.");

            } catch (\Throwable $e) {

                Log::error("Error ejecutando regla {$regla->id}");
                Log::error($e->getMessage());
                Log::error($e->getTraceAsString());

            }
        }

        Log::info('FIN EJECUCIÓN DE TAREAS PROGRAMADAS');
        Log::info('========================================');
    }
    public function ejecutarTarea(FormLogicRule $regla)
    {
        $ejecucion = FormLogicExecution::create([
            'rule_id' => $regla->id,
            'estado' => 'ejecutando',
            'inicio' => now(),
        ]);

        try {

            EjecutarLogicaFormulario::dispatch(
                collect([$regla]),
                [],
                'scheduled',
                null,
                env('APP_URL'),
                false
            );

            $ejecucion->update([
                'estado' => 'correcto',
                'fin' => now(),
            ]);

        } catch (\Throwable $e) {

            $ejecucion->update([
                'estado' => 'error',
                'fin' => now(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function debeEjecutarseAhora(FormLogicRule $regla)
    {
        $p = $regla->parametros['programacion'];

        $ahora = now();

        /*
        |--------------------------------------------------------------------------
        | Vigencia
        |--------------------------------------------------------------------------
        */

        if (!empty($p['inicio']) && $ahora->lt($p['inicio'])) {
            return false;
        }

        if (!empty($p['fin']) && $ahora->gt($p['fin'])) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Última ejecución
        |--------------------------------------------------------------------------
        */

        $ultima = $regla->ejecuciones->first();

        switch ($p['tipo']) {

            case 'daily':

                if ($ahora->format('H:i') != $p['hora']) {
                    return false;
                }

                if (
                    $ultima &&
                    $ultima->inicio->isToday() &&
                    $ultima->inicio->format('H:i') == $p['hora']
                ) {
                    return false;
                }

                return true;


            case 'weekly':

                if (
                    !in_array($ahora->dayOfWeek, $p['dias'] ?? [])
                    || $ahora->format('H:i') != $p['hora']
                ) {
                    return false;
                }

                if (
                    $ultima &&
                    $ultima->inicio->isSameDay($ahora)
                ) {
                    return false;
                }

                return true;


            case 'monthly':

                if (
                    $ahora->day != ($p['dia_mes'] ?? 0)
                    || $ahora->format('H:i') != $p['hora']
                ) {
                    return false;
                }

                if (
                    $ultima &&
                    $ultima->inicio->month == $ahora->month &&
                    $ultima->inicio->year == $ahora->year
                ) {
                    return false;
                }

                return true;


            case 'once':

                if (
                    $ahora->toDateString() != ($p['fecha'] ?? null)
                    || $ahora->format('H:i') != $p['hora']
                ) {
                    return false;
                }

                return !$ultima;


            case 'interval':

                if (!$ultima) {
                    return true;
                }

                $cada = (int) ($p['cada'] ?? 1);

                return match ($p['unidad']) {
                    'minutes' => $ultima->inicio->copy()->addMinutes($cada)->lte($ahora),
                    'hours' => $ultima->inicio->copy()->addHours($cada)->lte($ahora),
                    default => false,
                };
        }

        return false;
    }
    public function LogicaEliminarRespuesta($evento, $respuesta, $esCascada = false, &$visitados = [])
    {

        Log::info('Eliminar', [
            'respuesta' => $respuesta->id,
            'visitados' => array_keys($visitados),
        ]);
        if (isset($visitados[$respuesta->id])) {
            return [
                'success' => true
            ];
        }

        $visitados[$respuesta->id] = true;


        $form = $respuesta->form_id;

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


        $errores = array_filter(
            $this->ValidarLogica($respuesta, $filas, $evento),
            fn($msg) => !empty(trim($msg))
        );


        if (!empty($errores)) {
            return [
                'success' => false,
                'errores' => array_values($errores)
            ];
        }



        $this->EjecutarAcciones($agrupadas, $evento, $esCascada);


        $respuestasHijas = $this->obtenerRespuestasHijas($respuesta);

        $errores = [];

        foreach ($respuestasHijas as $respuestaHija) {

            $resultado = $this->LogicaEliminarRespuesta(
                $evento,
                $respuestaHija,
                true,
                $visitados
            );

            if (!$resultado['success']) {
                $errores = array_merge($errores, $resultado['errores'] ?? []);
            }
        }
        $errores = array_values(array_unique($errores));

        if (!empty($errores)) {
            return [
                'success' => false,
                'errores' => $errores,
            ];
        }

        $this->FormularioRepository->EliminarArchivos($respuesta);

        $respuesta->camposRespuestas()->delete();

        $respuesta->delete();


        return [
            'success' => true,
            'mensaje' => configForm($form, 'messages.success_delete')
        ];
    }

    public function obtenerRespuestasHijas(RespuestasForm $respuestaPadre)
    {
        $respuestaPadre->loadMissing('camposRespuestas');

        $resultado = collect();

        // Buscar asociaciones donde participa el formulario
        $asociaciones = FormularioAsociacion::all()->filter(function ($asoc) use ($respuestaPadre) {

            return collect($asoc->formularios)
                ->pluck('id')
                ->contains($respuestaPadre->form_id);

        });

        foreach ($asociaciones as $asociacion) {

            foreach ($asociacion->config ?? [] as $regla) {

                // Solo reglas de relación
                if (
                    empty($regla['relacion_multiple']) ||
                    ($regla['modo'] ?? '') !== 'asignacion'
                ) {
                    continue;
                }

                $destino = $regla['destino'] ?? [];
                $formula = $regla['formula'][1] ?? null;

                if (!$formula || $formula['tipo'] !== 'campo') {
                    continue;
                }

                /*
                 * Ejemplo:
                 *
                 * destino.form = 4
                 * destino.campo = 15
                 *
                 * formula.form = 3
                 * formula.campo = 11
                 */

                $formPadre = (int) $formula['form'];
                $campoPadre = (int) $formula['campo_id'];

                $formHijo = (int) $destino['form'];
                $campoHijo = (int) $destino['campo_id'];

                // Esta regla no corresponde a esta respuesta
                if ($respuestaPadre->form_id != $formPadre) {
                    continue;
                }

                // Valor del campo padre
                $campoRespuesta = $respuestaPadre->camposRespuestas
                    ->firstWhere('cf_id', $campoPadre);

                if (!$campoRespuesta) {
                    continue;
                }

                $valor = $campoRespuesta->valor;

                // Buscar respuestas hijas
                $ids = RespuestasCampo::where('cf_id', $campoHijo)
                    ->where('valor', $valor)
                    ->pluck('respuesta_id');

                $resultado = $resultado->merge(

                    RespuestasForm::where('form_id', $formHijo)
                        ->whereIn('id', $ids)
                        ->get()

                );
            }
        }

        return $resultado->unique('id')->values();
    }
}
