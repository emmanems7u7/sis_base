<input type="date" data-campo-id="{{ $campo->id }}" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}"
    data-caso="{{ $caso }}" class="form-control"
    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('Y-m-d') : '' }}" {{ $esRequerido ? 'required' : '' }}>