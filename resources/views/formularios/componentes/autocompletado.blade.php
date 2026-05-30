<input type="hidden" data-campo-id="{{ $campo->id }}" data-etiqueta="{{ $etiqueta }}"
    data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    class="campo-autocompletado {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    value="{{ old($inputName, $campo->config['autocompletar'] ?? '') }}"
    data-default="{{ $campo->config['autocompletar'] ?? '' }}">

@if ($errors->has($inputName))
    <div class="invalid-feedback d-block">
        {{ $errors->first($inputName) }}
    </div>
@endif