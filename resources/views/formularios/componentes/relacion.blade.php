<input type="text" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" id="{{ $inputId }}"
    class="form-control campo-relacion {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    data-campo-id="{{ $campo->id }}" value="{{ old($inputName, $valor) }}" readonly>

@if($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif