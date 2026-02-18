@can($formulario->id . '.ver')
    <a href="#" class="btn btn-xs btn-info btn-ver-respuesta" data-form-id="{{ $formulario->id }}"
        data-respuesta-id="{{ $respuesta->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver registro">
        <i class="fas fa-eye"></i>
    </a>
@endcan

@can($formulario->id . '.editar')
    <a href="{{ route('respuestas.edit', ['respuesta' => $respuesta, 'modulo' => $modulo]) }}"
        class="btn btn-xs btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar registro">
        <i class="fas fa-pencil-alt"></i>
    </a>
@endcan

@can($formulario->id . '.eliminar')
    <a href="#" class="btn btn-xs btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar registro"
        onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
        <i class="fas fa-trash-alt"></i>
    </a>

    <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST" action="{{ route('respuestas.destroy', $respuesta) }}"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endcan