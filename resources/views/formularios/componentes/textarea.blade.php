<textarea data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" id="{{ $inputId }}"
    class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    placeholder="{{ $campo->config['placeholder'] ?? '' }}" {{ $esRequerido ? 'required' : '' }}>{{ old($inputName, $valor) }}</textarea>

@if($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif