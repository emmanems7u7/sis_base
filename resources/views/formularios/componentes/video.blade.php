<div class="preview-archivo mb-2">
    @if($valor)
        <a href="{{ asset('archivos/formulario_' . $form . '/videos/' . $valor) }}" target="_blank">
            <i class="fas fa-video"></i> Ver video
        </a>
    @endif
</div>

<input type="file" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" id="{{ $inputId }}" accept="video/*"
    class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}" {{ $esRequerido ? 'required' : '' }}>

@if($errors->has($inputName))
    <div class="invalid-feedback d-block">
        {{ $errors->first($inputName) }}
    </div>
@endif