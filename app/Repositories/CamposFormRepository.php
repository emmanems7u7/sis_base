<?php

namespace App\Repositories;

use App\Interfaces\CamposFormInterface;
use App\Models\CamposForm;
use App\Interfaces\CatalogoInterface;
use Illuminate\Support\Str;
class CamposFormRepository implements CamposFormInterface
{
    protected $CatalogoRepository;

    public function __construct(CatalogoInterface $catalogoInterface)
    {
        $this->CatalogoRepository = $catalogoInterface;

    }


    public function obtenerOpcionesCompletas($campos)
    {
        $resultado = collect();

        foreach ($campos as $campo) {

            if ($campo->categoria_id) {
                // Trae todos los catálogos activos de la categoría
                $campo->opciones_catalogo = $this->CatalogoRepository
                    ->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
            } elseif ($campo->form_ref_id) {
                // Campo que referencia a otro formulario
                $campoReferencia = CamposForm::where('form_id', $campo->form_ref_id)
                    ->orderBy('posicion', 'asc')
                    ->first();

                if ($campoReferencia) {
                    $campo->opciones_catalogo = $campo->opcionesFormularioQuery() // Query Builder
                        ->get()
                        ->map(function ($respuesta) use ($campoReferencia) {
                            $valorCampo = $respuesta->camposRespuestas
                                ->firstWhere('cf_id', $campoReferencia->id);

                            return (object) [
                                'catalogo_codigo' => $respuesta->id,
                                'catalogo_descripcion' => $valorCampo->valor ?? 'Sin nombre',
                            ];
                        });
                } else {
                    $campo->opciones_catalogo = collect();
                }
            } else {
                $campo->opciones_catalogo = collect();
            }

            $resultado->push($campo);
        }

        return $resultado;
    }

    public function CrearCampoForm($request, $formulario)
    {
        $posicion = CamposForm::where('form_id', $request->form_id)->max('posicion') + 1;

        $campo = CamposForm::create([
            'form_id' => $formulario->id,
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'posicion' => $posicion,
            'config' => $request->config ?? [],
            'form_ref_id' => $request->formulario_id ?: null,
        ]);

        return $campo;
    }

    public function EditarCampoForm($request, $campo)
    {
        $campo->update([
            'tipo' => $request->tipo,
            'nombre' => Str::of($request->nombre)->replace(' ', '_'),
            'etiqueta' => $request->etiqueta,
            'requerido' => $request->requerido ? 1 : 0,
            'categoria_id' => $request->categoria_id ?: null,
            'config' => $request->config ?? $campo->config,
            'form_ref_id' => $request->formulario_id ?: null,
        ]);

        return $campo;
    }
}
