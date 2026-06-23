@can($formulario->id . '.ver')
    <a href="#" class="btn btn-xs btn-info btn-ver-respuesta" data-form-id="{{ $formulario->id }}"
        data-respuesta-id="{{ $respuesta->id }}" data-bs-toggle="tooltip" data-bs-placement="top"
        title="{{ configForm($formulario->id, 'buttons.view', null, 'text', 'none') }}">
        {!! configForm($formulario->id, 'buttons.view', null, 'icon') !!}
    </a>
@endcan

@can($formulario->id . '.editar')
    <a href="{{ route('respuestas.edit', ['respuesta' => $respuesta, 'modulo' => $modulo]) }}" class="btn btn-xs btn-warning"
        data-bs-toggle="tooltip" data-bs-placement="top"
        title="{{ configForm($formulario->id, 'buttons.edit', null, 'text', 'none') }}">
        {!! configForm($formulario->id, 'buttons.edit', null, 'icon') !!}
    </a>
@endcan

@can($formulario->id . '.eliminar')
    <a href="#" class="btn btn-xs btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
        title="{{ configForm($formulario->id, 'buttons.delete', null, 'text', 'none') }}"
        onclick="confirmarEliminacion(
                                                'eliminarRespuesta_{{ $respuesta->id }}',
                                                '{{ $respuesta->grupo
                                                    ? 'Este registro forma parte de un grupo. Es mejor ir al registro principal. ¿Deseas continuar?'
                                                    : '¿Estás seguro de que deseas eliminar esta respuesta?' }}',
                                                {{ $respuesta->grupo
                                                    ? "function(){ window.location.href='" .
                                                        route('respuestas.edit', ['respuesta' => $respuesta, 'modulo' => $modulo]) .
                                                        "'; }"
                                                    : 'null' }}
                                            )">

        {!! configForm($formulario->id, 'buttons.delete', null, 'icon') !!}
    </a>

    <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST" action="{{ route('respuestas.destroy', $respuesta) }}"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endcan
