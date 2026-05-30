<div class="d-flex align-items-center gap-2">

    <select name="{{ $inputName }}" id="{{ $inputId }}"
        class="form-select tom-select campo-dinamico {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
        data-etiqueta="{{ $etiqueta }}" data-tipo="{{ $campo->campo_nombre }}" data-campo-id="{{ $campo->id }}" {{ $esRequerido ? 'required' : '' }}>

        <option value="">Seleccione...</option>

        @foreach($campo->opciones_catalogo as $opcion)
            <option value="{{ $opcion->catalogo_codigo }}" {{ old($inputName, $valor) == $opcion->catalogo_codigo ? 'selected' : '' }}>
                {{ $opcion->catalogo_descripcion }}
            </option>
        @endforeach

    </select>


</div>

@if($errors->has($inputName))
    <div class="invalid-feedback d-block">
        {{ $errors->first($inputName) }}
    </div>
@endif