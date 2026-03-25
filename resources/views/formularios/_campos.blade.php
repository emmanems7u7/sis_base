
<div id="formulario-dinamico">
<div class="row g-4">
@foreach($campos as $campo)
@php
    $tipo = strtolower($campo->campo_nombre);

    $tiposSinLayout = ['campo autocompletado'];
    $esHidden = in_array($tipo, $tiposSinLayout);

    $cols = $cols ?? 2;
    $colSize = intval(12 / max(1, min(12, $cols)));
    $colClass = $tipo === 'textarea' ? 'col-12' : "col-md-{$colSize}";

    $esRequerido = false;
    if(isset($formulario->config['registro_multiple']) && !$formulario->config['registro_multiple']) {
        $esRequerido = isset($requerido)
            ? (bool) $requerido
            : (bool) $campo->requerido;
    }

    $mostrarAsterisco = isset($requerido)
        ? (bool) $requerido
        : (bool) $campo->requerido;

    $valoresCampo = $valores[$campo->nombre] ?? [];
    $valor = old($campo->nombre, $valoresCampo[0] ?? '');
@endphp

@if(!$esHidden)
<div class="{{ $colClass }}">
@endif

    @if(!$esHidden)
        <label class="form-label fw-bold">
            {{ $campo->etiqueta }}
            @if($mostrarAsterisco)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    @switch($tipo)

        {{-- INPUTS BÁSICOS --}}
        @case('text')
        @case('email')
        @case('number')
        @case('password')
        @case('enlace')
          @include('formularios.componentes.input_basico')
        @break

        {{-- TEXTAREA --}}
        @case('textarea')
          @include('formularios.componentes.textarea')
            
        @break

        {{-- FECHA --}}
        @case('fecha')
          @include('formularios.componentes.fecha')
           
        @break

        {{-- HORA --}}

        @case('hora')
          @include('formularios.componentes.hora')
           
        @break

        {{-- SELECTOR --}}
        @case('selector')
          @include('formularios.componentes.selector')
           
        @break

        {{-- CHECKBOX --}}
        @case('checkbox')
          @include('formularios.componentes.checkbox')
            
        @break

        {{-- RADIO --}}
        @case('radio')
          @include('formularios.componentes.radio')
            
        @break

        {{-- ARCHIVOS --}}
        @case('archivo')
        @include('formularios.componentes.archivo')
        
        @break
        {{-- IMAGEN --}}

        @case('imagen')
        @include('formularios.componentes.imagen')
           
        @break
        {{-- VIDEO --}}

        @case('video')
        @include('formularios.componentes.video')
           
        @break

        {{-- COLOR --}}
        @case('color')
        @include('formularios.componentes.color')

        @break

        {{-- AUTOCOMPLETADO (HIDDEN) --}}
        @case('campo autocompletado')
        @include('formularios.componentes.autocompletado')
        @break

        {{-- CAMPO RELACION --}}
        @case('campo_relacion')
        @include('formularios.componentes.relacion')

        @break

    @endswitch

@if(!$esHidden)
</div>
@endif

@endforeach


</div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {

document.addEventListener('change', function (e) {

    const select = e.target;

    // Solo para selects dinámicos
    if (!select.classList.contains('campo-dinamico')) return;

    // 🔥 Buscar el formulario contenedor
    const form = select.closest('form');

    // 🔥 Verificar si el formulario tiene al menos un campo-relacion
    if (!form || !form.querySelector('.campo-relacion')) return;

    const valorSeleccionado = select.value;
    const campoId = select.dataset.campoId;
    const nombreCampo = select.name;

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

        form.querySelectorAll(`[data-campo-id="${data.campo_referencia}"]`)
            .forEach(input => {

                if (input.type === 'radio') {
                    input.checked = input.value == data.valor;
                } else {
                    input.value = data.valor ?? '';
                }

            });

    })
    .catch(err => console.error('Error:', err));

});

});
</script>


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



