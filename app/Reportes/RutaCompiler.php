<?php

namespace App\Reportes;

use App\Models\CamposForm;
use App\Models\Formulario;
use App\Reportes\RutaNodo;
class RutaCompiler
{
    /**
     * Cache de campos
     */
    protected array $campos = [];

    /**
     * Compila una ruta
     *
     * 16
     * 15.11
     * 16.3.26
     */
    public function compile(Formulario $formulario, string $ruta): array
    {

        $segmentos = explode('.', $ruta);

        return $this->resolverRuta(
            $formulario,
            $segmentos
        );
    }

    /**
     * Resuelve cada segmento.
     */
    protected function resolverRuta(
        Formulario $formulario,
        array $segmentos
    ): array {

        $resultado = [];

        foreach ($segmentos as $i => $campoId) {


            $campo = $this->obtenerCampo($campoId);


            if (!$campo) {
                throw new \Exception(
                    "No existe el campo {$campoId}"
                );
            }



            $nodo = new RutaNodo();


            $nodo->indice = $i;

            $nodo->campo = $campo;

            /*
            El campo pertenece al formulario actual
            */
            $nodo->formulario = $formulario;



            /*
            Si es relación
            */
            if ($campo->form_ref_id) {


                $nodo->esRelacion = true;


                $nodo->formularioRelacion =
                    Formulario::find(
                        $campo->form_ref_id
                    );


                if (!$nodo->formularioRelacion) {

                    throw new \Exception(
                        "No existe formulario {$campo->form_ref_id}"
                    );

                }


                $nodo->campoClave =
                    $this->obtenerCampoClave(
                        $nodo->formularioRelacion
                    );


            } else {


                $nodo->esRelacion = false;


            }



            $resultado[] = $nodo;



            /*
            El siguiente campo se busca dentro
            del formulario relacionado
            */

            if ($campo->form_ref_id) {


                $formulario =
                    $nodo->formularioRelacion;


            }


        }



        foreach ($resultado as $i => $nodo) {

            $nodo->anterior =
                $resultado[$i - 1] ?? null;


            $nodo->siguiente =
                $resultado[$i + 1] ?? null;

        }


        return $resultado;

    }
    /**
     * Cache de campos
     */
    protected function obtenerCampo(
        $id
    ): ?CamposForm {

        if (!isset($this->campos[$id])) {

            $this->campos[$id] = CamposForm::find($id);

        }

        return $this->campos[$id];

    }
    protected function obtenerCampoClave(
        Formulario $formulario
    ): ?CamposForm {
        return $formulario
            ->campos()
            ->orderBy('posicion')
            ->first();
    }
}