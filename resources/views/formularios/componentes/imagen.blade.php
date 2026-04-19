<div class="preview-archivo mb-2">
    @if($valor)
        <a href="{{ asset('archivos/formulario_' . $form . '/imagenes/' . $valor) }}"
            data-fancybox="imagenes_{{ $campo->id }}" data-caption="Imagen" class="text-primary">
            <i class="fas fa-image"></i> Ver imagen
        </a>
    @endif
</div>

<input type="file" data-tipo="{{ $campo->campo_nombre }}" name="{{ $inputName }}" accept="image/*"
    class="form-control {{ $errors->has($inputName) ? 'is-invalid' : '' }}" {{ $esRequerido ? 'required' : '' }}>

@if($errors->has($inputName))
    <div class="invalid-feedback d-block">
        {{ $errors->first($inputName) }}
    </div>
@endif