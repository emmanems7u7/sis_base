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
}
