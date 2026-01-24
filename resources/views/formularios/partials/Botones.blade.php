@can($formulario->id . '.editar')
    <a href="{{ route('respuestas.edit', ['respuesta' => $respuesta, 'modulo' => $modulo]) }}"
        class="btn btn-sm btn-warning">
        <i class="fas fa-pencil-alt"></i> Editar
    </a>
@endcan
@can($formulario->id . '.eliminar')

    <a href="#" class="btn btn-sm btn-danger"
        onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
        <i class="fas fa-trash-alt"></i> Eliminar
    </a>

    <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST" action="{{ route('respuestas.destroy', $respuesta) }}"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>


@endcan