<input type="{{ $tipo === 'enlace' ? 'url' : $tipo }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    id="{{ $inputId }}" class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    value="{{ old($inputName, $valor) }}" placeholder="{{ $campo->config['placeholder'] ?? '' }}" {{ $esRequerido ? 'required' : '' }}>

@if($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif