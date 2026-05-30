<input type="hidden" data-etiqueta="{{ $etiqueta }}" data-campo-id="{{ $campo->id }}"
    data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" id="{{ $inputId }}" class="form-control"
    value="{{ $valor }}" placeholder="{{ $campo->config['placeholder'] ?? '' }}" {{ $esRequerido ? 'required' : '' }}>