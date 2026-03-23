<input type="date" name="{{ $campo->nombre }}" class="form-control"
    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('Y-m-d') : '' }}" {{ $esRequerido ? 'required' : '' }}>