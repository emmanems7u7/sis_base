<?php

namespace App\Http\Controllers;

use App\Models\FormConfiguration;
use Illuminate\Http\Request;
use App\Interfaces\FormConfigInterface;
use App\Models\Formulario;

class FormConfigurationController extends Controller
{

    protected $formConfigInterface;
    public function __construct(
        FormConfigInterface $formConfigInterface,

    ) {

        $this->formConfigInterface = $formConfigInterface;



    }


    public function edit($formularioId)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Configuración', 'url' => null],
        ];

        $formulario = Formulario::findOrFail($formularioId);

        /*
        |--------------------------------------------------------------------------
        | SI NO EXISTE CONFIGURACIÓN LA CREA AUTOMÁTICAMENTE
        |--------------------------------------------------------------------------
        */

        $config = FormConfiguration::firstOrCreate(['formulario_id' => $formularioId], ['config' => []]);

        $fields = $this->defaultFields($config);

        return view('form_configurations.edit', compact('formulario', 'config', 'breadcrumb', 'fields'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $formularioId)
    {

        $config = FormConfiguration::firstOrCreate(['formulario_id' => $formularioId], ['config' => []]);

        $config->update(['config' => $this->buildConfig($request)]);

        /*
        |--------------------------------------------------------------------------
        | LIMPIAR CACHE
        |--------------------------------------------------------------------------
        */

        $this->formConfigInterface->clear($formularioId);

        return redirect()->back()->with('status', 'Configuración actualizada correctamente');
    }

    private function buildConfig($request)
    {
        $result = [];

        /*
        |--------------------------------------------------------------------------
        | DEFAULTS
        |--------------------------------------------------------------------------
        */

        foreach ($request->defaults ?? [] as $key => $value) {

            data_set(
                $result,
                $key,
                $value
            );
        }

        /*
 |--------------------------------------------------------------------------
 | CUSTOM
 |--------------------------------------------------------------------------
 */

        $duplicates = [];

        foreach ($request->custom ?? [] as $custom) {

            $type = trim($custom['type'] ?? '');
            $key = trim($custom['key'] ?? '');

            if (!$type || !$key) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDAR DUPLICADOS
            |--------------------------------------------------------------------------
            */

            $unique = $type . '.' . $key;

            if (in_array($unique, $duplicates)) {

                throw \Illuminate\Validation\ValidationException::withMessages([

                    'custom' => "La configuración '{$unique}' está duplicada."

                ]);
            }

            $duplicates[] = $unique;

            /*
            |--------------------------------------------------------------------------
            | GUARDAR
            |--------------------------------------------------------------------------
            */

            $result[$type][$key] = [

                'text' => $custom['value'] ?? null,
                'icon' => $custom['icon'] ?? null,

            ];
        }

        return $result;
    }


    private function defaultFields($config)
    {
        // 1. fuente única
        $base = config('forms');

        $result = [];

        // 2. normalizar config base
        foreach ($base as $section => $items) {

            $sectionKey = strtoupper($section);

            $result[$sectionKey] = [];

            foreach ($items as $key => $value) {

                $result[$sectionKey][] = [
                    'label' => $value['label'] ?? $value['text'] ?? $key,
                    'text' => $value['text'] ?? null,
                    'icon' => $value['icon'] ?? null,
                    'key' => $section . '.' . $key,
                ];
            }
        }

        // 3. merge desde BD (override dinámico)
        if ($config) {

            foreach ($config->config ?? [] as $type => $items) {

                $section = strtoupper($type);

                if (!isset($result[$section])) {
                    $result[$section] = [];
                }

                $existingKeys = array_column($result[$section], 'key');

                foreach ($items as $key => $value) {

                    $fullKey = $type . '.' . $key;

                    if (in_array($fullKey, $existingKeys)) {
                        continue;
                    }

                    $result[$section][] = [
                        'label' => $fullKey,
                        'text' => null,
                        'icon' => null,
                        'key' => $fullKey,
                    ];
                }
            }
        }

        return $result;
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormConfiguration $formConfiguration)
    {
        //
    }
}
