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
use App\Models\Formulario;

class FormLogicRepository implements FormLogicInterface
{
    protected $CatalogoRepository;

    public function __construct(
        CatalogoInterface $catalogoInterface,

    ) {

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

        if (!$formDestino) {
            $mensaje = "No existe formulario destino para la acción {$action->id}";
            Log::warning($mensaje);
            return $mensaje;
        }

        $mensaje = '';

        $accion = $this->CatalogoRepository->getNombreCatalogo($tipoAccion);

        switch ($tipoAccion) {

            case 'TAC-001': // modificar_campo
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

            // Agregar más acciones según sea necesario
        }

        return $mensaje;
    }

}
