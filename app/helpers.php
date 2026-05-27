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
        $mode = 'normal'
    ) {


        $config = formConfigCache($formularioId);


        $item = data_get(
            $config,
            $key,
            data_get(config('forms'), $key)
        );



        if (!$item) {
            return $default;
        }



        if (
            is_array($item)
            && array_key_exists('text', $item)
        ) {

            $text = e($item['text'] ?? '');

            $icon = '';



            if (!empty($item['icon'])) {

                $icon = '<i class="' . e($item['icon']) . '"></i>';
            }



            switch ($mode) {

                //SOLO ICONO

                case 'icon':

                    return $icon;

                //SOLO TEXTO

                case 'text':

                    return $text;

                //MOBILE

                case 'mobile':

                    return $icon . '<br><span class="btn-text">' . $text . '</span>';

                //NORMAL

                default:

                    return $icon . ' <span class="btn-text">' . $text . '</span>';
            }
        }

        //STRING NORMAL

        return $item;
    }
}

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