@if($valor)
    <a href="{{ asset('archivos/formulario_' . $form . '/archivos/' . $valor) }}" target="_blank"
        class="btn btn-sm btn-outline-primary mb-2">Ver archivo</a>
@endif
<input type="file" name="{{ $campo->nombre }}" class="form-control" {{ $esRequerido ? 'required' : '' }}>