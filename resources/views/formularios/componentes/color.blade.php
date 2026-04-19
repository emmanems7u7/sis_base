<input type="color" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" value="{{ old($inputName, $valor) }}"
    class="form-control form-control-color {{ $errors->has($inputName) ? 'is-invalid' : '' }}">

@if($errors->has($inputName))
    <div class="invalid-feedback d-block">
        {{ $errors->first($inputName) }}
    </div>
@endif