@include('formularios.partials.accion_eliminar_masivo')

@can($formulario->id . '.eliminar')


    <button data-bs-toggle="tooltip" data-bs-placement="top" data-form_id="{{ $formulario->id }}" title="Eliminar registro"
        id="btn-eliminar-masivo_{{ $formulario->id }}" class="d-none btn btn-danger btn-xs text-white"
        onclick="confirmarEliminacion('form-eliminar-masivo_{{ $formulario->id }}', '¿Estás seguro de que deseas estas respuestas?, la acción no puede deshacerse.')"
        disabled style="position: fixed; bottom: 70px; right: 35px; z-index: 1050;">
        <i class="fas fa-trash-alt"></i>
    </button>

    <form id="form-eliminar-masivo" method="POST" action="{{ route('respuestas.eliminarMasivo') }}" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="respuestas_ids" id="respuestas_ids">
    </form>


@endcan

<div class="row mt-2">

    @can($formulario->id . '.eliminar')
        <div class="card shadow-sm check-col d-none mt-1 mb-1">
            <div class="card-body">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input p-0" type="checkbox" id="check-todos" style="width:16px; height:16px;">
                    <label class="form-check-label mb-0" for="check-todos">Todos</label>
                </div>

            </div>
        </div>
    @endcan

    @include('formularios.partials.iterador_cards', ['modulo_id' => 0])

</div>