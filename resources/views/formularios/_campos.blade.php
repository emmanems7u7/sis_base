
<div id="formulario-dinamico">
<div class="row g-4">
@foreach($campos as $campo)
@php
    /* ===============================
       COLUMNAS
    =============================== */
    $cols = $cols ?? 2;
    $colSize = intval(12 / max(1, min(12, $cols)));

    if (strtolower($campo->campo_nombre) === 'textarea') {
        $colClass = 'col-12';
    } else {
        $colClass = "col-md-{$colSize}";
    }

    /* ===============================
       REQUERIDO (sobrescribible)
    =============================== */
    // si se envía por include → manda
    // si no → usa el valor del campo

    if(isset($formulario->config['registro_multiple']) && !$formulario->config['registro_multiple'])
    {

        $esRequerido = isset($requerido)
        ? (bool) $requerido
        : (bool) $campo->requerido;
    }
    else{
        $esRequerido = false;
    }
   
    $esRequerido_ = isset($requerido)
        ? (bool) $requerido
        : (bool) $campo->requerido;
    /* ===============================
       VALOR
    =============================== */
    $valoresCampo = $valores[$campo->nombre] ?? [];
    $valor = old($campo->nombre, $valoresCampo[0] ?? '');
@endphp


    <div class="{{ $colClass }}">
    @if($campo->campo_nombre != 'campo autocompletado')
 
        <label class="form-label fw-bold">
            {{ $campo->etiqueta }} {{  $esRequerido }}
            @if($esRequerido_)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
        @switch(strtolower($campo->campo_nombre))

            {{-- TEXTO --}}
            @case('text')
                <input type="text" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- NUMBER --}}
            @case('number')
                <input type="number" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- TEXTAREA --}}
            @case('textarea')
                <textarea name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>{{ $valor }}</textarea>
            @break

            {{-- CHECKBOX --}}
            @case('checkbox')
                @php
                    $checkedValues = (array) old($campo->nombre, $valoresCampo);
                @endphp

                <div class="opciones-container" data-campo-id="{{ $campo->id }}">
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="checkbox"
                                name="{{ $campo->nombre }}[]"
                                value="{{ $opcion->catalogo_codigo }}"
                                class="form-check-input campo-formulario"
                                id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                {{ in_array($opcion->catalogo_codigo, $checkedValues) ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="button"
                    class="btn btn-sm btn-primary mt-2 btn-ver-mas-checkbox"
                    data-campo-id="{{ $campo->id }}">
                    Ver más...
                </button>
            @break

            {{-- RADIO --}}
            @case('radio')
                <div class="radio-container" data-campo-id="{{ $campo->id }}">
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="radio"
                                name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                                value="{{ $opcion->catalogo_codigo }}"
                                class="form-check-input campo-formulario"
                                id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                {{ $valor == $opcion->catalogo_codigo ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach

                    <button type="button" class="btn btn-sm btn-primary btn-ver-mas mt-2">
                        Ver más...
                    </button>
                </div>
            @break

            {{-- SELECTOR --}}
            @case('selector')
                <div class="d-flex align-items-center gap-2">
                    <select name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                        class="form-select tom-select campo-dinamico"
                        data-campo-id="{{ $campo->id }}"
                       {{ $esRequerido ? 'required' : '' }}>
                        <option value="">Seleccione...</option>
                        @foreach($campo->opciones_catalogo as $opcion)
                            <option value="{{ $opcion->catalogo_codigo }}"
                                {{ $valor == $opcion->catalogo_codigo ? 'selected' : '' }}>
                                {{ $opcion->catalogo_descripcion }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button"
                        class="btn btn-outline-secondary btn-sm btn-buscar-opcion"
                        data-bs-toggle="modal"
                        data-bs-target="#modalBuscarOpcion"
                        data-campo-id="{{ $campo->id }}">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            @break

            {{-- EMAIL --}}
            @case('email')
                <input type="email" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- PASSWORD --}}
            @case('password')
                <input type="password" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- FECHA --}}
            @case('fecha')
                <input type="date" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('Y-m-d') : '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- HORA --}}
            @case('hora')
                <input type="time" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('H:i') : '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- ARCHIVO --}}
            @case('archivo')

            @if($valor)
                <div class="mb-2">
                    <a href="{{ asset('archivos/formulario_'.$form.'/archivos/'.$valor) }}"
                    target="_blank"
                    class="btn btn-outline-primary btn-sm">
                        Ver archivo actual
                    </a>
                </div>
            @endif

            <div id="preview_{{ $campo->nombre }}" class="mb-2"></div>

            <input type="file"
                name="{{ $campo->nombre }}"
                id="{{ $campo->nombre }}"
                data-preview="preview_{{ $campo->nombre }}"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>

            @break

            {{-- IMAGEN --}}
            @case('imagen')

            @if($valor)
                <div class="mb-2">
                    <img src="{{ asset('archivos/formulario_'.$form.'/imagenes/'.$valor) }}"
                        class="img-thumbnail"
                        style="max-height: 150px">
                </div>
            @endif

            <div id="preview_{{ $campo->nombre }}" class="mb-2"></div>

            <input type="file"
                name="{{ $campo->nombre }}"
                id="{{ $campo->nombre }}"
                accept="image/*"
                data-preview="preview_{{ $campo->nombre }}"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>

            @break

            {{-- VIDEO --}}
            @case('video')

            @if($valor)
                <div class="mb-2">
                    <video controls style="max-width: 100%; max-height: 200px">
                        <source src="{{ asset('archivos/formulario_'.$form.'/videos/'.$valor) }}">
                        Tu navegador no soporta video.
                    </video>
                </div>
            @endif

            <div id="preview_{{ $campo->nombre }}" class="mb-2"></div>

            <input type="file"
                name="{{ $campo->nombre }}"
                id="{{ $campo->nombre }}"
                accept="video/*"
                data-preview="preview_{{ $campo->nombre }}"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>

            @break


            {{-- ENLACE --}}
            @case('enlace')
                <input type="url" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="https://..."
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- COLOR --}}
            @case('color')
                <input type="color" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control form-control-color"
                    value="{{ $valor }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break



            @case('campo autocompletado')
                <input type="hidden" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control campo-autocompletado"
                    value="{{ $campo->config['autocompletar'] }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}
                   data-campo-id="{{ $campo->id }}">
            @break


            @case('campo_relacion')
              <input type="text" name="{{ $campo->nombre }}" id="{{ $campo->nombre }}" 
                    class="form-control"
                    data-campo-id="{{ $campo->id }}"
                    value=""
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }} readonly>



                   <script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.campo-dinamico').forEach(select => {

        select.addEventListener('change', function () {

            const valorSeleccionado = this.value;
            const campoId = this.dataset.campoId;
            const nombreCampo = this.name;

            if (!valorSeleccionado) return;

            fetch("{{ route('campos.obtenerData') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content")
                },
                body: JSON.stringify({
                    campo_id: campoId,
                    nombre: nombreCampo,
                    valor: valorSeleccionado
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // 🔥 Buscar el input que tenga el campo_referencia
                const inputRelacionado = document.querySelector(
                    `[data-campo-id="${data.campo_referencia}"]`
                );

                if (inputRelacionado) {
                    inputRelacionado.value = data.valor ?? '';
                }

               
            })
            .catch(err => console.error('Error:', err));

        });

    });

});
</script>

            @break
        @endswitch
    </div>
@endforeach

</div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('input[type="file"]').forEach(input => {

        input.addEventListener('change', function (e) {

            const file = e.target.files[0];
            const previewId = e.target.dataset.preview;
            const previewContainer = document.getElementById(previewId);

            if (!file || !previewContainer) return;

            previewContainer.innerHTML = '';
            const reader = new FileReader();

            reader.onload = function (event) {

                if (file.type.startsWith('image/')) {

                    previewContainer.innerHTML = `
                        <img src="${event.target.result}"
                             class="img-thumbnail"
                             style="max-height:150px;">
                    `;

                } else if (file.type.startsWith('video/')) {

                    previewContainer.innerHTML = `
                        <video controls style="max-width:100%; max-height:200px">
                            <source src="${event.target.result}">
                        </video>
                    `;

                } else {

                    previewContainer.innerHTML = `
                        <a href="${event.target.result}"
                           target="_blank"
                           class="btn btn-outline-secondary btn-sm">
                           Ver archivo seleccionado
                        </a>
                    `;
                }
            };

            reader.readAsDataURL(file);
        });
    });

});
</script>
@include('formularios.campos.modal_busqueda')



