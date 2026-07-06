<?php

namespace App\Repositories;

use App\Interfaces\FormConfigInterface;

use App\Models\FormConfiguration;
use Illuminate\Support\Facades\Cache;

class FormConfigRepository implements FormConfigInterface
{
    public function get($formularioId)
    {
        return Cache::rememberForever(
            "form_config_{$formularioId}",
            function () use ($formularioId) {

                $config = FormConfiguration::where(
                    'formulario_id',
                    $formularioId
                )->first();

                return $config?->config ?? [];
            }
        );
    }

    public function value($formularioId, $key)
    {
        $config = $this->get($formularioId);

        return data_get($config, $key)
            ?? data_get(config('forms'), $key);
    }

    public function clear($formularioId)
    {
        Cache::forget("form_config_{$formularioId}");
    }




    public function buildConfig($request)
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


    public function defaultFields($config)
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
}
