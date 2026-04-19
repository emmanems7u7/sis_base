<input type="text" data-tipo="identificador" data-campo-id="{{ $campo->id }}" data-caso="{{ $caso }}"
    id="identificador_{{ $campo->id }}_{{ $inputId }}" name="{{ $inputName }}"
    class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}" value="{{ old($inputName, $valor) }}"
    readonly>

@if($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif