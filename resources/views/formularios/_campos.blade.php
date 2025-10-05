<div class="row g-4">
    @foreach($campos as $campo)
        @php
            $colClass = strtolower($campo->campo_nombre) === 'textarea' ? 'col-12' : 'col-md-6';
            $valoresCampo = $valores[$campo->nombre] ?? []; // valores opcionales enviados desde la vista
        @endphp

        <div class="{{ $colClass }}">
            <label class="form-label fw-bold">
                {{ $campo->etiqueta }} 
                @if($campo->requerido) <span class="text-danger">*</span> @endif
            </label>

            @switch(strtolower($campo->campo_nombre))
                @case('text')
                    <input type="text" name="{{ $campo->nombre }}" class="form-control"
                        placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                        value="{{ $valoresCampo[0] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('number')
                    <input type="number" name="{{ $campo->nombre }}" class="form-control"
                        placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                        value="{{ $valoresCampo[0] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('textarea')
                    <textarea name="{{ $campo->nombre }}" class="form-control"
                        placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>{{ $valoresCampo[0] ?? '' }}</textarea>
                @break

                @case('checkbox')
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $campo->nombre }}[]" 
                                   value="{{ $opcion->catalogo_codigo }}" 
                                   class="form-check-input" 
                                   id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                   {{ in_array($opcion->catalogo_codigo, $valoresCampo) ? 'checked' : '' }}>
                            <label class="form-check-label" 
                                   for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach
                @break

                @case('radio')
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="radio" name="{{ $campo->nombre }}" 
                                   value="{{ $opcion->catalogo_codigo }}" 
                                   class="form-check-input" 
                                   id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                   {{ ($valoresCampo[0] ?? '') == $opcion->catalogo_codigo ? 'checked' : '' }}>
                            <label class="form-check-label" 
                                   for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach
                @break

                @case('selector')
                    <select name="{{ $campo->nombre }}" class="form-select" {{ $campo->requerido ? 'required' : '' }}>
                        <option value="">Seleccione una opción</option>
                        @foreach($campo->opciones_catalogo as $opcion)
                            <option value="{{ $opcion->catalogo_codigo }}"
                                {{ ($valoresCampo[0] ?? '') == $opcion->catalogo_codigo ? 'selected' : '' }}>
                                {{ $opcion->catalogo_descripcion }}
                            </option>
                        @endforeach
                    </select>
                @break

                @case('imagen')
                    <div class="mb-2">
                        @if(isset($valoresCampo) && count($valoresCampo) > 0)
                            @foreach($valoresCampo as $img)
                                <img src="{{ asset('archivos/formulario_'.$form.'/imagenes/'.$img) }}" alt="Imagen actual" class="img-fluid rounded mb-1" style="max-width: 150px;">
                            @endforeach
                        @endif
                    </div>
                    <input type="file" name="{{ $campo->nombre }}" accept="image/*" class="form-control preview-image" multiple {{ $campo->requerido ? 'required' : '' }}>
                    <div class="mt-2" id="preview-{{ $campo->nombre }}"></div>
                @break

                @case('video')
                    <div class="mb-2">
                        @if(isset($valoresCampo) && count($valoresCampo) > 0)
                            @foreach($valoresCampo as $video)
                                <video controls class="mb-1" style="max-width: 100%; height: auto;">
                                    <source src="{{ asset('archivos/formulario_'.$form.'/videos/'.$video) }}" type="video/mp4">
                                    Tu navegador no soporta videos.
                                </video>
                            @endforeach
                        @endif
                    </div>
                    <input type="file" name="{{ $campo->nombre }}" accept="video/*" class="form-control preview-video" multiple {{ $campo->requerido ? 'required' : '' }}>
                    <div class="mt-2" id="preview-video-{{ $campo->nombre }}"></div>
                @break

                @case('enlace')
                    <input type="url" name="{{ $campo->nombre }}" class="form-control"
                        placeholder="https://..."
                        value="{{ $valoresCampo[0] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('fecha')
                    <input type="date" name="{{ $campo->nombre }}" class="form-control"
                        value="{{ isset($valoresCampo[0]) ? \Carbon\Carbon::parse($valoresCampo[0])->format('Y-m-d') : '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('hora')
                    <input type="time" name="{{ $campo->nombre }}" class="form-control"
                        value="{{ isset($valoresCampo[0]) ? \Carbon\Carbon::parse($valoresCampo[0])->format('H:i') : '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('archivo')
                    <input type="file" name="{{ $campo->nombre }}" class="form-control"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('color')
                    <input type="color" name="{{ $campo->nombre }}" class="form-control form-control-color"
                        value="{{ $valoresCampo[0] ?? '#000000' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('email')
                    <input type="email" name="{{ $campo->nombre }}" class="form-control"
                        value="{{ $valoresCampo[0] ?? '' }}"
                        placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break

                @case('password')
                    <input type="password" name="{{ $campo->nombre }}" class="form-control"
                        placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                        {{ $campo->requerido ? 'required' : '' }}>
                @break


                @default
                    <input type="text" name="{{ $campo->nombre }}" class="form-control"
                           value="{{ $valoresCampo[0] ?? '' }}">
            @endswitch
        </div>
    @endforeach
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Previsualizador de imágenes
    document.querySelectorAll('.preview-image').forEach(input => {
        input.addEventListener('change', function() {
            const previewDiv = document.getElementById('preview-' + input.name.replace('[]',''));
            previewDiv.innerHTML = '';
            Array.from(input.files).forEach(file => {
                if(file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '150px';
                        img.classList.add('rounded', 'me-1', 'mb-1');
                        previewDiv.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });

    // Previsualizador de videos
    document.querySelectorAll('.preview-video').forEach(input => {
        input.addEventListener('change', function() {
            const previewDiv = document.getElementById('preview-video-' + input.name.replace('[]',''));
            previewDiv.innerHTML = '';
            Array.from(input.files).forEach(file => {
                if(file.type.startsWith('video/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.style.maxWidth = '100%';
                        video.style.height = 'auto';
                        video.classList.add('mb-1');
                        previewDiv.appendChild(video);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
});

</script>