@php
    $valores = $respuesta->camposRespuestas
        ->where('cf_id', $campo->id)
        ->pluck('valor')
        ->toArray();

    $tipoCampo = strtolower($campo->campo_nombre);
    $displayValores = [];

    foreach ($valores as $v) {

        switch ($tipoCampo) {



            case 'imagen':

                $displayValores[] = "
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <a href='" . asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") . "'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               data-fancybox='gallery'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               data-caption='Imagen'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               class='text-primary me-2'>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class='fas fa-image'></i> Ver imagen
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ";

                break;

            case 'video':
                $displayValores[] = "
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <a href='" . asset("archivos/formulario_{$formulario->id}/videos/{$v}") . "'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               target='_blank'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               class='text-primary me-2'>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <i class='fas fa-video'></i> Ver video
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ";
                break;

            case 'archivo':
                $displayValores[] = "
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <a href='" . asset("archivos/formulario_{$formulario->id}/archivos/{$v}") . "'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    target='_blank'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    class='text-primary me-2'>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class='fas fa-file'></i> Descargar
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </a>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                ";
                break;

            case 'enlace':
                $displayValores[] = "<a href='{$v}' target='_blank'><i class='fas fa-link'></i> Ver enlace</a>";
                break;

            case 'fecha':
                $displayValores[] = \Carbon\Carbon::parse($v)->format('d/m/Y');
                break;

            case 'hora':
                $displayValores[] = $v;
                break;

            case 'password':
                $displayValores[] = '*****';
                break;

            default:
                $displayValores[] = e($v);
        }
    }
@endphp


@if($isMobile)
    <div class="col-6 col-md-6 mb-1">
        <div class="border-bottom pb-1" style="font-size: 0.85rem;">
            <strong class="d-block">{{ $campo->etiqueta }}:</strong>
            <span class="text-muted">
                {!! implode(', ', $displayValores) !!}
            </span>
        </div>
    </div>
@else
    <td>{!! implode('<br>', $displayValores) !!}</td>

@endif