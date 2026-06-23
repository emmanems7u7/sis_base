<?php

use App\Models\Configuracion;
use App\Models\FormConfiguration;
use Illuminate\Support\Facades\Cache;

/*
 TWO FACTOR
*/

if (!function_exists('twoFactorGlobalEnabled')) {

    function twoFactorGlobalEnabled(): bool
    {
        return Configuracion::first()?->doble_factor_autenticacion ?? false;
    }
}



/*
 configForm()

 Obtiene configuraciones dinámicas de formularios desde BD.
 Si no existe configuración personalizada usa:

 config/forms.php

 MODOS

 normal -> icono + texto
 text   -> solo texto
 icon   -> solo icono
 mobile -> icono + <br> + texto
 TAGS:
 span (default), p, h1, h2, h3, h4, h5, h6, none
 ---------------------------------------------------------
 EJEMPLOS

 {!! configForm($formulario->id, 'buttons.save') !!}

 {{ configForm($formulario->id, 'buttons.save', null, 'text') }}

 {!! configForm($formulario->id, 'buttons.save', null, 'icon') !!}

 {!! configForm($formulario->id, 'buttons.save', null, 'mobile') !!}

 {{ configForm($formulario->id, 'messages.success_create') }}

*/
if (!function_exists('configForm')) {

    function configForm(
        $formularioId,
        $key,
        $default = null,
        $mode = 'normal',
        $tag = 'span'
    ) {

        $config = formConfigCache($formularioId);

        $defaultItem = data_get(
            config('forms'),
            $key,
            []
        );

        $item = data_get(
            $config,
            $key,
            []
        );

        // Merge config BD + config base
        if (is_array($defaultItem) && is_array($item)) {

            $item = array_merge(
                $defaultItem,
                array_filter(
                    $item,
                    fn($value) => !is_null($value)
                )
            );
        }

        if (empty($item)) {
            $item = $defaultItem;
        }

        if (!$item) {
            return $default;
        }

        /*
        TEXT RENDER CON TAG
        */
        if (is_array($item) && array_key_exists('text', $item)) {

            $textValue = e($item['text'] ?? '');

            // ========= ICON =========
            $icon = '';

            if (!empty($item['icon'])) {
                $icon = '<i class="' . e($item['icon']) . '"></i>';
            }

            // ========= TEXT WITH TAG =========
            if ($tag === 'none') {
                $text = $textValue;
            } else {

                $allowedTags = ['span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

                if (!in_array($tag, $allowedTags)) {
                    $tag = 'span';
                }

                $text = "<{$tag} class='btn-text'>{$textValue}</{$tag}>";
            }

            // ========= MODES =========
            switch ($mode) {

                case 'icon':
                    return $icon;

                case 'text':
                    return $text;

                case 'mobile':
                    return $icon . '<br>' . $text;

                default:
                    return $icon . ' ' . $text;
            }
        }

        // STRING SIMPLE
        return $item;
    }
}

/*
 CACHE FORM CONFIG
*/
if (!function_exists('formConfigCache')) {

    function formConfigCache($formularioId)
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
}