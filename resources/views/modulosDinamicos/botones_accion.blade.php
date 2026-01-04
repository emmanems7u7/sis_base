{{-- Izquierda: Registrar, Exportar, Carga Masiva --}}
<div class="d-flex gap-2 flex-wrap">
    {{-- Registrar --}}
    <a href="{{ route('formularios.registrar', ['form' => $formulario, 'modulo' => $modulo]) }}"
        class="btn btn-sm btn-success">
        <i class="fas fa-plus me-1"></i> Registrar
    </a>

    {{-- Exportar --}}
    <div class="btn-group" role="group">
        <button id="btnGroupExport" type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fas fa-file-export me-1"></i> Exportar
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
    <a target="_blank" href="{{ route('formularios.carga_masiva', $formulario) }}" class="btn btn-sm btn-warning">
        <i class="fas fa-upload me-1"></i> Carga Masiva
    </a>
</div>

{{-- Derecha: Buscar --}}
<div>
    <button type="button" class="btn btn-sm btn-light " data-bs-toggle="modal"
        data-bs-target="#modal_busqueda_{{ $formulario->id }}">
        <i class="fas fa-search me-1"></i> Buscar
    </button>
</div>