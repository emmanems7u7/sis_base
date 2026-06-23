<div class="row w-100 g-2 justify-content-center">

    @can($formulario->id . '.ver')
        <div class="col-4">
            <a href="#" class="btn btn-xs btn-outline-info w-100 btn-ver-respuesta btn-mobile-small"
                data-form-id="{{ $formulario->id }}" data-respuesta-id="{{ $respuesta->id }}">

                {!! configForm($formulario->id, 'buttons.view', null, 'mobile') !!}
            </a>
        </div>
    @endcan

    @can($formulario->id . '.editar')
        <div class="col-4">
            <a href="{{ route('respuestas.edit', ['respuesta' => $respuesta->id, 'modulo' => $modulo]) }}"
                class="btn btn-outline-warning btn-xs w-100 btn-accion btn-mobile-small">

                {!! configForm($formulario->id, 'buttons.edit', null, 'mobile') !!}
            </a>
        </div>
    @endcan

    @can($formulario->id . '.eliminar')
        <div class="col-4">
            <a href="#" class="btn btn-outline-danger btn-xs w-100 btn-accion btn-mobile-small"
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
                {!! configForm($formulario->id, 'buttons.delete', null, 'mobile') !!}
            </a>

            <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST"
                action="{{ route('respuestas.destroy', $respuesta) }}" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    @endcan

</div>
