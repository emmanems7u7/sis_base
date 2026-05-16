@php
    $rid = '__RESPUESTAID__';
    $fid = '__FORMID__';

@endphp

<div class="row w-100 g-2 justify-content-center">

    @can($formulario->id . '.ver')
        <div class="col-4">
            <a href="#" class="btn btn-xs btn-outline-info  w-100 btn-ver-respuesta" data-form-id="{{ $fid }}"
                data-respuesta-id="{{ $rid }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver registro">
                <i class="fas fa-eye"></i> <br> Ver
            </a>
        </div>
    @endcan


    @can($formulario->id . '.editar')
        <div class="col-4">
            <a href="{{ route('respuestas.edit', ['respuesta' => $rid, 'modulo' => $modulo]) }}"
                class="btn btn-outline-warning btn-xs w-100 btn-accion">
                <i class="fas fa-pencil-alt me-1"></i> <br> Editar
            </a>
        </div>
    @endcan


    @can($formulario->id . '.eliminar')
        <div class="col-4">
            <a href="#" class="btn btn-outline-danger btn-xs w-100 btn-accion"
                onclick="confirmarEliminacion('eliminarRespuesta_{{ $rid }}', '¿Estás seguro?')">
                <i class="fas fa-trash-alt me-1"></i> <br>Eliminar
            </a>
        </div>

        <form id="eliminarRespuesta_{{ $rid }}" method="POST" action="{{ route('respuestas.destroy', $rid) }}"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

</div>