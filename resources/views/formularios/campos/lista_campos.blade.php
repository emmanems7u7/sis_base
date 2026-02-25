<label class="form-label small mb-1">
    {{ $campo->etiqueta }}
    @if($campo->requerido)
        <span class="text-danger">*</span>
    @endif
   
</label>
@if($campo->campo_nombre == 'campo autocompletado')
<i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Este campo es de tipo Hidden, por lo tanto no será visible en el formulario para el usuario. Sin embargo, se completará automáticamente con el valor que usted seleccione para su autocompletado."></i>
   
   @endif

   @if($campo->campo_nombre == 'campo_relacion')
<i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Este campo es de tipo referencial, se registrara el contenido del campo seleccionado correspondiente al formulario con el que se tiene la relación"></i>
   
   @endif
@switch(strtolower($campo->campo_nombre))

    @case('text')
        <input type="text" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('number')
        <input type="number" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('textarea')
        <textarea name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1" 
            rows="2"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}"></textarea>
        @break

    @case('checkbox')
        <div class="opciones-container mb-1" data-campo-id="{{ $campo->id }}">
            @foreach($campo->opciones_catalogo as $opcion)
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="{{ $campo->nombre }}[]" 
                        value="{{ $opcion->catalogo_codigo }}" 
                        class="form-check-input form-check-input-sm"
                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    <label class="form-check-label small" 
                        for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </label>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-xs btn-primary mt-1 btn-ver-mas-checkbox" 
            data-campo-id="{{ $campo->id }}">
            Ver más
        </button>
        @break

    @case('radio')
        <div class="radio-container mb-1" data-campo-id="{{ $campo->id }}">
            @foreach($campo->opciones_catalogo as $opcion)
                <div class="form-check form-check-inline">
                    <input type="radio" name="{{ $campo->nombre }}" 
                        value="{{ $opcion->catalogo_codigo }}" 
                        class="form-check-input form-check-input-sm"
                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    <label class="form-check-label small" 
                        for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </label>
                </div>
            @endforeach
            <button type="button" class="btn btn-xs btn-primary mt-1 btn-ver-mas">
                Ver más
            </button>
        </div>
        @break

    @case('selector')
        <div class="d-flex align-items-center gap-1 mb-1">
            <select name="{{ $campo->nombre }}" 
                class="form-select form-select-sm tom-select campo-dinamico" 
                data-campo-id="{{ $campo->id }}">
                <option value="">Seleccione...</option>
                @foreach($campo->opciones_catalogo as $opcion)
                    <option value="{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary btn-xs btn-buscar-opcion" 
                data-bs-toggle="modal" 
                data-bs-target="#modalBuscarOpcion"
                data-campo-id="{{ $campo->id }}">
                <i class="fas fa-search"></i>
            </button>
        </div>
        @break

    @case('imagen')
        <input type="file" name="{{ $campo->nombre }}" accept="image/*" 
            class="form-control form-control-sm mb-1" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('video')
        <input type="file" name="{{ $campo->nombre }}" accept="video/*" 
            class="form-control form-control-sm mb-1" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('enlace')
        <input type="url" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="https://...">
        @break

    @case('fecha')
        <input type="date" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('hora')
        <input type="time" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('archivo')
        <input type="file" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('color')
        <input type="color" name="{{ $campo->nombre }}" 
            class="form-control form-control-color form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('email')
        <input type="email" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('password')
        <input type="password" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('campo autocompletado')
      
            <input type="text" name="{{ $campo->nombre }}" 
                class="form-control form-control-sm mb-1 campo-autocompletado" 
                data-campo-id="{{ $campo->id }}"
                placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                value="{{ $campo->config['autocompletar'] ?? ''; }}">  
            <button class="btn btn-xs btn-dark btn_autocompletado" >Guardar</button>´


            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    document.querySelectorAll('.btn_autocompletado').forEach(button => {

                        button.addEventListener('click', function (e) {
                            e.preventDefault();

                            const input = this.previousElementSibling;

                            const campoId = input.dataset.campoId;
                            const nombreCampo = input.name;
                            const valor = input.value;

                            fetch('/guardar/campo/autocompletado', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    
                                    campo_id: campoId,
                                    nombre: nombreCampo,
                                    valor: valor
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(data.success)
                                {
                                    input.value = data.valor;
                                    alertify.success(data.message);
                                }
                            
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });

                        });

                    });

                });
                </script>

                            @break


                            @case('campo_relacion')

                            <script>
                                const formulariosRef = @json($formularios_ref);
                                const relacionActual = @json($campo->config['relacion'] ?? null);
                            </script>

                            <div class="mb-3">
                                <label class="form-label">Formulario relacionado</label>
                                <select id="selectFormulario" class="form-select">
                                    <option value="">Seleccione un formulario</option>
                                    @foreach($formularios_ref as $form)
                                        <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Campos del formulario</label>
                                <select id="selectCampos" class="form-select">
                                    <option value="">Seleccione un campo</option>
                                </select>
                            </div>

                            <button class="btn btn-xs btn-dark boton_relacion">Guardar</button>´
                        

                            <script>
document.addEventListener('DOMContentLoaded', function () {

    const selectFormulario = document.getElementById('selectFormulario');
    const selectCampos = document.getElementById('selectCampos');
    const boton = document.querySelector('.boton_relacion');

    // ===============================
    // 🔹 Función reutilizable
    // ===============================
    function cargarCampos(formId, campoSeleccionado = null) {

        selectCampos.innerHTML = '<option value="">Seleccione un campo</option>';

        if (!formId) return;

        const formulario = formulariosRef.find(f => f.id == formId);
        if (!formulario) return;

        formulario.campos.forEach(campo => {

            const option = document.createElement('option');
            option.value = campo.id;
            option.textContent = campo.etiqueta;

            if (campoSeleccionado && campo.id == campoSeleccionado) {
                option.selected = true;
            }

            selectCampos.appendChild(option);
        });
    }

    // ===============================
    // 🔹 Evento cambio formulario
    // ===============================
    selectFormulario.addEventListener('change', function () {
        cargarCampos(this.value);
    });

    // ===============================
    // 🔹 Autocargar relación existente
    // ===============================
    if (typeof relacionActual !== 'undefined' && relacionActual) {

        selectFormulario.value = relacionActual.form_ref_id;

        cargarCampos(
            relacionActual.form_ref_id,
            relacionActual.campo_ref_id
        );
    }

    // ===============================
    // 🔹 Guardar relación
    // ===============================
    boton.addEventListener('click', function (e) {
        e.preventDefault();

        const formularioId = selectFormulario.value;
        const campoId = selectCampos.value;

        if (!formularioId || !campoId) {
            alertify.warning('Debe seleccionar formulario y campo');
            return;
        }

        fetch("{{ route('campos.guardarRelacion') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content")
            },
            body: JSON.stringify({
                campo_principal_id: {{ $campo->id ?? 'null' }},
                form_ref_id: formularioId,
                campo_ref_id: campoId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertify.success(data.message);
            } else {
                alertify.error('Error al guardar');
            }
        })
        .catch(() => alertify.error('Error de servidor'));
    });

});
</script>

                            @break

    @default
        <input type="text" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1">
@endswitch