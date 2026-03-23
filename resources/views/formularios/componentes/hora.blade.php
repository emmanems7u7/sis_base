<input type="time" name="{{ $campo->nombre }}" class="form-control"
    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('H:i') : '' }}" {{ $esRequerido ? 'required' : '' }}>