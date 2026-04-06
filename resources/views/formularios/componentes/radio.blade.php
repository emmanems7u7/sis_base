<div class="radio-container" data-campo-id="{{ $campo->id }}">

    @foreach($campo->opciones_catalogo as $opcion)

        @php
            $inputId = $prefix 
                ? "{$prefix}_{$campo->nombre}_{$opcion->catalogo_codigo}" 
                : "{$campo->nombre}_{$opcion->catalogo_codigo}";
        @endphp

        <div class="form-check">
            <input 
                type="radio"
                data-tipo="{{ $campo->campo_nombre }}"
                name="{{ $inputName }}"
                value="{{ $opcion->catalogo_codigo }}"
                class="form-check-input campo-formulario"
                id="{{ $inputId }}"
                {{ $valor == $opcion->catalogo_codigo ? 'checked' : '' }}
            >

            <label class="form-check-label" for="{{ $inputId }}">
                {{ $opcion->catalogo_descripcion }}
            </label>
        </div>

    @endforeach

    <button type="button" class="btn btn-sm btn-outline-primary btn-ver-mas mt-2">
        Ver más...
    </button>

</div>