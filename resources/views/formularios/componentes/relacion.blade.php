<input type="text" data-etiqueta="{{ $etiqueta }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    id="{{ $inputId }}"
    class="form-control campo-relacion campo-readonly {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    data-campo-id="{{ $campo->id }}" value="{{ old($inputName, $valor) }}" readonly>

@if ($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif

<style>
    .campo-readonly {
        background-color: #eceff1;
        border-color: #ced4da;
        cursor: pointer;
    }
</style>
