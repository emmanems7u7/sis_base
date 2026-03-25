<div class="preview-archivo mb-2">
    @if($valor)
        <a href="{{ asset('archivos/formulario_' . $form . '/archivos/' . $valor) }}" target="_blank">
            <i class="fas fa-file"></i> Ver archivo
        </a>
    @endif
</div>


<input type="file" data-tipo="{{ $campo->campo_nombre }}" name="{{ $campo->nombre }}" class="form-control" {{ $esRequerido ? 'required' : '' }}>