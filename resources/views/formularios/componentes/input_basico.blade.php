<input type="{{ $tipo === 'enlace' ? 'url' : $tipo }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    id="{{ $inputId }}" class="form-control" value="{{ $valor }}"
    placeholder="{{ $campo->config['placeholder'] ?? '' }}" {{ $esRequerido ? 'required' : '' }}>