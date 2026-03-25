<div class="preview-archivo mb-2">
    @if($valor)
        <a href="{{ asset('archivos/formulario_' . $form . '/videos/' . $valor) }}" target="_blank">
            <i class="fas fa-video"></i> Ver video
        </a>
    @endif
</div>
<input type="file" data-tipo="{{ $campo->campo_nombre }}" name="{{ $campo->nombre }}" accept="video/*"
    class="form-control" {{ $esRequerido ? 'required' : '' }}>