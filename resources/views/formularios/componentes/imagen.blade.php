@if($valor)
    <img src="{{ asset('archivos/formulario_' . $form . '/imagenes/' . $valor) }}" class="img-thumbnail mb-2"
        style="max-height:150px">
@endif
<input type="file" name="{{ $campo->nombre }}" accept="image/*" class="form-control" {{ $esRequerido ? 'required' : '' }}>