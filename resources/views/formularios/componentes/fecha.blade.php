<input type="date" data-campo-id="{{ $campo->id }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    data-caso="{{ $caso }}" class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}"
    value="{{ old($inputName, $valor ? \Carbon\Carbon::parse($valor)->format('Y-m-d') : '') }}" {{ $esRequerido ? 'required' : '' }}>

@if($errors->has($inputName))
    <div class="invalid-feedback">
        {{ $errors->first($inputName) }}
    </div>
@endif