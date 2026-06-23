<div class="tom-select-wrapper">

    <div class="ts-skeleton placeholder-glow">
        <div class="placeholder col-12" style="height: 38px;"></div>
    </div>

    <select name="{{ $inputName }}" id="{{ $inputId }}"
        class="tom-select campo-dinamico d-none {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
        data-etiqueta="{{ $etiqueta }}" data-tipo="{{ $campo->campo_nombre }}" data-campo-id="{{ $campo->id }}"
        {{ $esRequerido ? 'required' : '' }}>

        <option value="">Seleccione...</option>

        @foreach ($campo->opciones_catalogo as $opcion)
            <option value="{{ $opcion->catalogo_codigo }}"
                {{ old($inputName, $valor) == $opcion->catalogo_codigo ? 'selected' : '' }}>
                {{ $opcion->catalogo_descripcion }}
            </option>
        @endforeach

    </select>

</div>
