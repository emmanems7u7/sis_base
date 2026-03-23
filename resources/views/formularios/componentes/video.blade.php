@if($valor)
    <video controls style="max-height:200px" class="mb-2">
        <source src="{{ asset('archivos/formulario_' . $form . '/videos/' . $valor) }}">
    </video>
@endif
<input type="file" name="{{ $campo->nombre }}" accept="video/*" class="form-control" {{ $esRequerido ? 'required' : '' }}>