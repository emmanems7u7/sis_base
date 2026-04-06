@php
    $checkedValues = (array) old($inputName, $valoresCampo);

    $inputNameArray = $prefix
        ? "{$prefix}[{$campo->nombre}][]"
        : "{$campo->nombre}[]";


@endphp

<div class="opciones-container" data-campo-id="{{ $campo->id }}">
    <div class="row">
        @foreach($campo->opciones_catalogo as $opcion)
            @php
                $inputId = $prefix
                    ? "{$prefix}_{$campo->nombre}_{$opcion->catalogo_codigo}"
                    : "{$campo->nombre}_{$opcion->catalogo_codigo}";
            @endphp

            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-check">
                    <input type="checkbox" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputNameArray }}"
                        value="{{ $opcion->catalogo_codigo }}" class="form-check-input campo-formulario" id="{{ $inputId }}"
                        {{ in_array($opcion->catalogo_codigo, $checkedValues) ? 'checked' : '' }}>

                    <label class="form-check-label" for="{{ $inputId }}">
                        {{ $opcion->catalogo_descripcion }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary mt-2 btn-ver-mas-checkbox"
        data-campo-id="{{ $campo->id }}">
        Ver más...
    </button>
</div>