<div class="d-flex align-items-center gap-2">

    <select name="{{ $inputName }}" id="{{ $inputId }}" class="form-select tom-select campo-dinamico"
        data-tipo="{{ $campo->campo_nombre }}" data-campo-id="{{ $campo->id }}" {{ $esRequerido ? 'required' : '' }}>
        <option value="">Seleccione...</option>

        @foreach($campo->opciones_catalogo as $opcion)
            <option value="{{ $opcion->catalogo_codigo }}" {{ $valor == $opcion->catalogo_codigo ? 'selected' : '' }}>
                {{ $opcion->catalogo_descripcion }}
            </option>
        @endforeach

    </select>

    <button type="button" class="btn btn-outline-secondary btn-sm btn-buscar-opcion" data-bs-toggle="modal"
        data-bs-target="#modalBuscarOpcion" data-campo-id="{{ $campo->id }}">
        <i class="fas fa-search"></i>
    </button>

</div>