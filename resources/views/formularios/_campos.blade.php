

<div class="row g-4">
@foreach($campos as $campo)
    @php
        $colClass = strtolower($campo->campo_nombre) === 'textarea' ? 'col-12' : 'col-md-6';
        $valoresCampo = $valores[$campo->nombre] ?? [];
    @endphp

    <div class="{{ $colClass }}">
        <label class="form-label fw-bold">
            {{ $campo->etiqueta }}
            @if($campo->requerido) <span class="text-danger">*</span> @endif
        </label>

        @switch(strtolower($campo->campo_nombre))
            @case('text')
                <input type="text" 
                    name="{{ $campo->nombre }}" 
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                    value="{{ old($campo->nombre, $valoresCampo[0] ?? '') }}"
                    {{ $campo->requerido ? 'required' : '' }}>
            @break

            @case('number')
                <input type="number" 
                    name="{{ $campo->nombre }}" 
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                    value="{{ old($campo->nombre, $valoresCampo[0] ?? '') }}"
                    {{ $campo->requerido ? 'required' : '' }}>
            @break

            @case('textarea')
                <textarea name="{{ $campo->nombre }}" 
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                    {{ $campo->requerido ? 'required' : '' }}>{{ old($campo->nombre, $valoresCampo[0] ?? '') }}</textarea>
            @break

            @case('checkbox')
                @foreach($campo->opciones_catalogo as $opcion)
                    @php
                        $checkedValues = old($campo->nombre, $valoresCampo);
                    @endphp
                    <div class="form-check">
                        <input type="checkbox"
                            name="{{ $campo->nombre }}[]"
                            value="{{ $opcion->catalogo_codigo }}"
                            class="form-check-input campo-formulario"
                            data-form-id="{{ $campo->form_ref_id ?? '' }}"
                            data-nombre="{{ $campo->nombre }}"
                            id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                            {{ in_array($opcion->catalogo_codigo, (array)$checkedValues) ? 'checked' : '' }}>
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
                        <input type="radio"
                            name="{{ $campo->nombre }}"
                            value="{{ $opcion->catalogo_codigo }}"
                            class="form-check-input campo-formulario"
                            data-form-id="{{ $campo->form_ref_id ?? '' }}"
                            data-nombre="{{ $campo->nombre }}"
                            id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                            {{ old($campo->nombre, $valoresCampo[0] ?? '') == $opcion->catalogo_codigo ? 'checked' : '' }}>
                        <label class="form-check-label"
                            for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                            {{ $opcion->catalogo_descripcion }}
                        </label>
                    </div>
                @endforeach
            @break

            @case('selector')
                <select 
                    name="{{ $campo->nombre }}" 
                    class="form-select campo-formulario"
                    data-form-id="{{ $campo->form_ref_id ?? '' }}"
                    data-nombre="{{ $campo->nombre }}"
                    {{ $campo->requerido ? 'required' : '' }}>
                    <option value="">Seleccione una opción</option>
                    @foreach($campo->opciones_catalogo as $opcion)
                        <option value="{{ $opcion->catalogo_codigo }}"
                            {{ old($campo->nombre, $valoresCampo[0] ?? '') == $opcion->catalogo_codigo ? 'selected' : '' }}>
                            {{ $opcion->catalogo_descripcion }}
                        </option>
                    @endforeach
                </select>
            @break

            @case('email')
                <input type="email" 
                    name="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ old($campo->nombre, $valoresCampo[0] ?? '') }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                    {{ $campo->requerido ? 'required' : '' }}>
            @break

            @case('fecha')
                <input type="date" 
                    name="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ old($campo->nombre, isset($valoresCampo[0]) ? \Carbon\Carbon::parse($valoresCampo[0])->format('Y-m-d') : '') }}"
                    {{ $campo->requerido ? 'required' : '' }}>
            @break

            @case('hora')
                <input type="time" 
                    name="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ old($campo->nombre, isset($valoresCampo[0]) ? \Carbon\Carbon::parse($valoresCampo[0])->format('H:i') : '') }}"
                    {{ $campo->requerido ? 'required' : '' }}>
            @break
        @endswitch
    </div>
@endforeach

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const activarTooltips = () => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    };

    document.addEventListener('change', async (e) => {
        const el = e.target;

        if (el.classList.contains('campo-formulario') && el.dataset.formId) {
            const formId = el.dataset.formId;
            const respuestaId = el.value;

            if (!respuestaId) return;

            try {
                const res = await fetch(`/formularios/${formId}/respuestas/${respuestaId}`);
                const data = await res.json();

                if (data.error) {
                    console.warn("Error:", data.error);
                    return;
                }

                let tooltipHTML = '';
                const formularios = data.formularios ?? [{ nombre: 'Formulario: ' +data.nombre, datos: data.datos }];

                formularios.forEach(f => {
                    tooltipHTML += `<div class="text-start"><strong>${f.nombre}</strong><br>`;
                    for (const [campo, valor] of Object.entries(f.datos)) {
                        tooltipHTML += `${campo}: <em>${valor || '-'}</em><br>`;
                    }
                    tooltipHTML += `</div><hr>`;
                });

                // Buscar el label asociado al input
                const label = el.closest('div').querySelector('label');

                if (!label) return;

                // Buscar si ya existe el ícono
                let infoIcon = label.querySelector('.info-tooltip');
                if (!infoIcon) {
                    infoIcon = document.createElement('i');
                    infoIcon.className = 'fas fa-info-circle fa-lg text-primary ms-1 info-tooltip';
                    infoIcon.style.cursor = 'pointer';
                    label.appendChild(infoIcon);
                }

                // Actualizar contenido del tooltip
                infoIcon.setAttribute('data-bs-toggle', 'tooltip');
                infoIcon.setAttribute('data-bs-html', 'true');
                infoIcon.setAttribute('title', tooltipHTML);

                // Activar/reiniciar tooltip
                const existingTooltip = bootstrap.Tooltip.getInstance(infoIcon);
                if (existingTooltip) existingTooltip.dispose();
                new bootstrap.Tooltip(infoIcon);

            } catch (err) {
                alertify.error("Error al obtener datos del formulario");
            }
        }
    });

    activarTooltips();
});
</script>




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