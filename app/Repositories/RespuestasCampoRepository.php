<?php

namespace App\Repositories;

use App\Interfaces\RespuestasCampoInterface;
use App\Models\ModuloFormularioParalelo;
use App\Models\RespuestasCampo;
use App\Models\RespuestasForm;

class RespuestasCampoRepository implements RespuestasCampoInterface
{
    public function GetRespCampoByIdValor($campoId, $valor)
    {
        return RespuestasCampo::where('cf_id', $campoId)
            ->where('valor', $valor)
            ->first();
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
        $mapCampos = collect($campos)->keyBy('id');
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

        $derechaSinCorchetes = preg_replace('/\s*\[.*?\]/', '', $derecha);
        $derechaSinCorchetes = trim($derechaSinCorchetes);

        if ($izquierda === $derechaSinCorchetes) {
            return $izquierda;
        }

        return $texto;
    }
    private function resolverRelacionCompleta($valor, $campo, $respuesta)
    {
        $resolver = function ($id) use ($campo, $respuesta) {

            $fila = RespuestasForm::with('camposRespuestas.campo')->find($id);

            if ($fila) {
                return $this->mapearFila($fila);

            }

            $res = $this->obtenerRelacionMultiple($respuesta->form_id, $campo->form_ref_id);


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
}
