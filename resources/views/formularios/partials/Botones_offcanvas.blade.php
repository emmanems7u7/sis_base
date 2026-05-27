@php
    $rid = '__RESPUESTAID__';
    $fid = '__FORMID__';
    $rg = '__RESPUESTAGRUPO__';
@endphp
<div class="row w-100 g-2 justify-content-center">

    @can($formulario->id . '.ver')
        <div class="col-4">
            <a href="#" class="btn btn-xs btn-outline-info w-100 btn-ver-respuesta btn-mobile-small"
                data-form-id="{{ $fid }}" data-respuesta-id="{{ $rid }}" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{configForm($formulario->id, 'buttons.view', null, 'text') }}">

                {!! configForm($formulario->id, 'buttons.view', null, 'mobile') !!}
            </a>
        </div>
    @endcan

    @can($formulario->id . '.editar')
        <div class="col-4">
            <a href="{{ route('respuestas.edit', ['respuesta' => $rid, 'modulo' => $modulo]) }}"
                class="btn btn-outline-warning btn-xs w-100 btn-accion btn-mobile-small">

                {!! configForm($formulario->id, 'buttons.edit', null, 'mobile') !!}
            </a>
        </div>
    @endcan

    @can($formulario->id . '.eliminar')
        <div class="col-4">
            <a href="#" class="btn btn-xs btn-outline-danger w-100  btn-mobile-small" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{   configForm($formulario->id, 'buttons.delete', null, 'text') }}" onclick="confirmarEliminacion(
                                                                'eliminarRespuesta_{{ $rid }}',
                                                                '{{ $rg
            ? 'Este registro forma parte de un grupo. Es mejor ir al registro principal. ¿Deseas continuar?'
            : '¿Estás seguro de que deseas eliminar esta respuesta?' }}',
                                                                {{ $rg
            ? "function(){ window.location.href='" . route('respuestas.edit', ['respuesta' => $rid, 'modulo' => $modulo]) . "'; }"
            : 'null' }}
                                                            )">

                {!! configForm($formulario->id, 'buttons.delete', null, 'mobile') !!}
            </a>
        </div>

        <form id="eliminarRespuesta_{{ $rid }}" method="POST" action="{{ route('respuestas.destroy', $rid) }}"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

</div>