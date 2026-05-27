{{-- Izquierda: Registrar, Exportar, Carga Masiva --}}
<div class="d-flex gap-1 flex-wrap">
    {{-- Registrar --}}
    <a href="{{ route('formularios.registrar', ['form' => $formulario, 'modulo' => $modulo]) }}"
        class="btn btn-xs btn-success">
        {!! configForm($formulario->id, 'buttons.add') !!}
    </a>

    {{-- Exportar --}}
    <div class="btn-group" role="group">
        <button id="btnGroupExport" type="button" class="btn btn-xs btn-info dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false">
            {!! configForm($formulario->id, 'titles.export') !!}
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnGroupExport">
            <li>
                <a class="dropdown-item" target="_blank" href="{{ route('formularios.exportPdf', $formulario) }}">
                    <i class="fas fa-file-pdf text-danger me-1"></i> PDF
                </a>
            </li>
            <li>
                <a class="dropdown-item" target="_blank" href="{{ route('formularios.exportExcel', $formulario) }}">
                    <i class="fas fa-file-excel text-success me-1"></i> Excel
                </a>
            </li>
        </ul>
    </div>

    {{-- Carga Masiva --}}
    <a target="_blank" href="{{ route('formularios.carga_masiva', $formulario) }}" class="btn btn-xs btn-warning">
        {!! configForm($formulario->id, 'titles.import') !!}
    </a>

    @can($formulario->id . '.eliminar')
        <button type="button" id="activar-seleccion-masiva_{{ $formulario->id }}" class="btn btn-outline-secondary btn-xs"
            data-form_id="{{ $formulario->id }}">
            {!! configForm($formulario->id, 'titles.mass_selection') !!}
        </button>

        <button data-bs-toggle="tooltip" data-bs-placement="top" data-form_id="{{ $formulario->id }}"
            title="Eliminar registro" id="btn-eliminar-masivo_{{ $formulario->id }}"
            class="d-none btn btn-danger btn-xs text-white"
            onclick="confirmarEliminacion('form-eliminar-masivo_{{ $formulario->id }}', '¿Estás seguro de que deseas estas respuestas?, la acción no puede deshacerse.')"
            disabled>
            {!! configForm($formulario->id, 'buttons.delete', null, 'icon') !!}
        </button>

        <form id="form-eliminar-masivo_{{ $formulario->id }}" method="POST"
            action="{{ route('respuestas.eliminarMasivo') }}" style="display:none;">
            @csrf
            @method('DELETE')
            <input type="hidden" id="respuestas_ids_{{ $formulario->id }}" name="respuestas_ids">
        </form>
    @endcan

</div>

{{-- Derecha: Buscar --}}
<div>
    <button type="button" class="btn btn-xs btn-light " data-bs-toggle="modal"
        data-bs-target="#modal_busqueda_{{ $formulario->id }}">
        {!! configForm($formulario->id, 'buttons.search') !!}
    </button>
</div>