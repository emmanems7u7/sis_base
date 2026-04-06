<input type="time" data-campo-id="{{ $campo->id }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    data-caso="{{ $caso }}" class="form-control"
    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('H:i') : '' }}" {{ $esRequerido ? 'required' : '' }}>