<?php

namespace App\Repositories;

use App\Interfaces\FormLogicInterface;
use App\Models\CamposForm;
use App\Models\FormLogicRule;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormLogicRepository implements FormLogicInterface
{

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
                    $q->with(['campoDestino', 'conditions']);
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
        $formDestino = $action->formularioDestino;
        $campoDestino = $action->campoDestino;



        if (!$formDestino || !$campoDestino) {
            $mensaje = "No existe formulario o campo destino para la acción {$action->id}";
            Log::warning($mensaje);
            return $mensaje;
        }

        $respuesta_campo_id = $this->obtenerValorDespuesGuion($filasSeleccionadas, $campoDestino->nombre);

        if (empty($respuesta_campo_id)) {
            $mensaje = "No se encontraron valores después del guion para el campo {$campoDestino->nombre}";
            Log::warning($mensaje);
            return $mensaje;
        }

        // 🔹 Determinar valor

        if (($action->tipo_valor ?? 'static') === 'campo') {
            $campoOrigenId = $action->valor;
            $campoOrigen = RespuestasCampo::where('cf_id', $campoOrigenId)->first();
            $valor = $campoOrigen ? $campoOrigen->valor : null;



            if ($valor === null) {
                $mensaje = "El campo origen con ID {$campoOrigenId} no tiene valor";
                Log::warning($mensaje);
                return $mensaje;
            }
        } else {

            $valor = $action->valor;

        }

        // 🔹 Validar condiciones

        $mensaje = '';

        foreach ($action->conditions as $condicion) {
            $valor_origen = $respuestaOrigen->camposRespuestas()
                ->where('cf_id', $condicion->campo_condicion)
                ->first()
                ->valor ?? null;

            $valores_destino = RespuestasCampo::whereIn('id', $respuesta_campo_id)
                ->where('cf_id', $condicion->valor)
                ->get();

            foreach ($valores_destino as $va) {
                $valor_destino = $va->valor;

                switch ($condicion->operador) {
                    case '=':
                        if ($valor_origen != $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera IGUAL a '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera IGUAL a '{$valor_destino}'");
                        }
                        break;

                    case '!=':
                        if ($valor_origen == $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera DIFERENTE a '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera DIFERENTE a '{$valor_destino}'");
                        }
                        break;

                    case '>':
                        if ((float) $valor_origen <= (float) $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera MAYOR que '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera MAYOR que '{$valor_destino}'");
                        }
                        break;

                    case '<':
                        if ((float) $valor_origen >= (float) $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera MENOR que '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera MENOR que '{$valor_destino}'");
                        }
                        break;

                    case '>=':
                        if ((float) $valor_origen < (float) $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera MAYOR O IGUAL que '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera MAYOR O IGUAL que '{$valor_destino}'");
                        }
                        break;

                    case '<=':
                        if ((float) $valor_origen > (float) $valor_destino) {
                            $mensaje .= "Condición fallida: se esperaba que '{$valor_origen}' fuera MENOR O IGUAL que '{$valor_destino}'<br>";
                            Log::warning("Condición fallida: se esperaba que '{$valor_origen}' fuera MENOR O IGUAL que '{$valor_destino}'");
                        }
                        break;

                    case 'in':
                        $valores = is_array($valor_destino)
                            ? $valor_destino
                            : explode(',', (string) $valor_destino);
                        if (!in_array($valor_origen, $valores)) {
                            $mensaje .= "Condición fallida: el valor '{$valor_origen}' NO se encuentra en [" . implode(', ', $valores) . "]<br>";
                            Log::warning("Condición fallida: el valor '{$valor_origen}' NO se encuentra en [" . implode(', ', $valores) . "]");
                        }
                        break;

                    default:
                        Log::warning("Operador desconocido: {$condicion->operador}");
                        $mensaje .= "Operador desconocido: {$condicion->operador}<br>";
                        break;
                }
            }
        }

        if ($mensaje != '') {
            return $mensaje;
        }

        DB::transaction(function () use ($respuesta_campo_id, $campoDestino, $action, $valor) {

            $respuestaDestinos = RespuestasCampo::whereIn('id', $respuesta_campo_id)->get();

            foreach ($respuestaDestinos as $campoResp) {
                $CatalogoRepository = new CatalogoRepository();
                $operacion = $CatalogoRepository->getNombreCatalogo($action->operacion);

                switch ($operacion) {

                    case 'sumar':
                        $valor = is_numeric($valor) ? (float) $valor : 0;
                        $campoResp->valor = ((float) $campoResp->valor + $valor);
                        break;

                    case 'restar':
                        $valor = is_numeric($valor) ? (float) $valor : 0;
                        $campoResp->valor = ((float) $campoResp->valor - $valor);
                        break;

                    case 'multiplicar':
                        $valor = is_numeric($valor) ? (float) $valor : 1;
                        $campoResp->valor = ((float) $campoResp->valor * $valor);
                        break;

                    case 'dividir':
                        $valor = is_numeric($valor) ? (float) $valor : 1;
                        if ($valor != 0) {
                            $campoResp->valor = ((float) $campoResp->valor / $valor);
                        }
                        break;

                    case 'actualizar':
                    case 'asignar':
                        $campoResp->valor = $valor;
                        break;

                    case 'copiar':
                        // Para copiar, asumimos que $valor contiene el ID del campo a copiar
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
                        $campoResp->valor = $campoResp->valor
                            ? \Carbon\Carbon::parse($campoResp->valor)->addDays((int) $valor)->format('Y-m-d')
                            : null;
                        break;

                    case 'decrementar_fecha':
                        $campoResp->valor = $campoResp->valor
                            ? \Carbon\Carbon::parse($campoResp->valor)->subDays((int) $valor)->format('Y-m-d')
                            : null;
                        break;

                    default:
                        Log::warning("Operación desconocida: {$action->operacion}");
                        break;
                }

                $campoResp->save();
                Log::info("Regla ejecutada: Action ID {$action->id}, Campo {$campoDestino->nombre}, Valor: {$campoResp->valor}");
            }
        });
        return $mensaje;
    }
}
