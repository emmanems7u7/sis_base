@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])


        <div class="card mt-2">
            <div class="card-body">
                <h5>Reglas y Acciones para el registro</h5>
                @if(!empty($humanRules))
                    <div class="list-group">
                        @foreach($humanRules as $rule)
                            <div class="list-group-item">{!! $rule !!}</div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No hay reglas de lógica configuradas para este formulario.</p>
                @endif

            </div>
        </div>

        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                <form
                    action="{{ route('formularios.responder', ['form' => $formulario->id, 'modulo' => $modulo, 'tipo' => 0]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf


                    @include('formularios._campos', ['campos' => $formulario->campos->sortBy('posicion'), 'valores' => []])

                    @if(isset($formulario->config['registro_multiple']) && $formulario->config['registro_multiple'])

                        <button type="button" class="btn btn-success mt-3" id="btn-agregar-registro">
                            Agregar registro
                        </button>

                        <div class="mt-4">
                            <h5>Registros agregados</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tabla-registros">
                                    <thead>
                                        <tr id="thead-dinamico">
                                            <th>#</th>
                                            <!-- Se llenará dinámicamente -->
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <input type="hidden" name="registros_json" id="registros_json">
                        <div id="hidden_files_container"></div>



                    @endif


                    @if(!$moduloModelo)
                        <a href="{{ route('formularios.index') }}" class="btn btn-secondary mt-3"><i
                                class="fas fa-arrow-left me-1"></i>Volver</a>
                    @else
                        <a href="{{ route('modulo.index', $moduloModelo->id) }}" class="btn btn-secondary mt-3"><i
                                class="fas fa-arrow-left me-1"></i>Volver</a>
                    @endif

                    <button type="submit" class="btn btn-primary mt-3">Registrar</button>


                </form>
            </div>
        </div>

    </div>
    @if(isset($formulario->config['registro_multiple']) && $formulario->config['registro_multiple'])

        <script>
            let registros = [];
            let editIndex = null;
            document.getElementById('btn-agregar-registro').addEventListener('click', function () {

                let contenedor = document.getElementById('formulario-dinamico');
                let inputs = contenedor.querySelectorAll('input, select, textarea');

                let registro = {};
                let tieneError = false;
                let primerError = null;

                // =============================
                // Limpiar errores previos
                // =============================
                inputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });

                inputs.forEach(input => {

                    if (!input.name) return;

                    let key = input.name.replace('[]', '');

                    // =============================
                    // Obtener nombre visible desde el LABEL
                    // =============================
                    let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                    let label = grupo ? grupo.querySelector('label') : null;

                    let nombreCampo = label
                        ? label.innerText.replace('*', '').trim()
                        : 'Este campo';

                    // =============================
                    // VALIDACIÓN REQUERIDOS
                    // =============================
                    if (input.required) {

                        if (input.type === 'checkbox') {

                            let grupoChecks = contenedor.querySelectorAll(`[name="${input.name}"]:checked`);
                            if (grupoChecks.length === 0) {
                                tieneError = true;
                                input.classList.add('is-invalid');
                                if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                            }

                        }
                        else if (input.type === 'radio') {

                            let grupoRadios = contenedor.querySelectorAll(`[name="${input.name}"]:checked`);
                            if (grupoRadios.length === 0) {
                                tieneError = true;
                                if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                            }

                        }
                        else if (input.type === 'file') {

                            if (
                                input.files.length === 0 &&
                                !(editIndex !== null && registros[editIndex]?.[key])
                            ) {
                                tieneError = true;
                                input.classList.add('is-invalid');
                                if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                            }

                        }
                        else {

                            if (!input.value.trim()) {
                                tieneError = true;
                                input.classList.add('is-invalid');
                                if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                            }

                        }
                    }

                    // =============================
                    // CONSTRUCCIÓN DEL REGISTRO
                    // =============================

                    if (input.type === 'checkbox') {

                        if (!registro[key]) registro[key] = [];

                        if (input.checked) {
                            registro[key].push(input.value);
                        }

                    }
                    else if (input.type === 'radio') {

                        if (input.checked) {
                            registro[key] = input.value;
                        }

                    }
                    else if (input.type === 'file') {

                        if (input.files.length > 0) {

                            let file = input.files[0];

                            registro[key] = {
                                type: 'new',
                                file: file,
                                preview: URL.createObjectURL(file)
                            };

                        }
                        else if (editIndex !== null && registros[editIndex]?.[key]) {

                            registro[key] = registros[editIndex][key];

                        }

                    }
                    else {

                        registro[key] = input.value;

                    }

                });


                if (tieneError) {

                    alertify.error(primerError ?? 'Complete los campos obligatorios.');

                    let campoInvalido = contenedor.querySelector('.is-invalid');
                    if (campoInvalido) {
                        campoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        campoInvalido.focus();
                    }

                    return;
                }

                validacion(registro);


            });

            function validacion(registro) {

                let contenedor = document.getElementById('formulario-dinamico');

                let formData = new FormData();

                formData.append('_token', document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
                );

                Object.keys(registro).forEach(key => {

                    let value = registro[key];

                    if (Array.isArray(value)) {

                        value.forEach(v => {
                            formData.append(key + '[]', v);
                        });

                    }
                    else if (typeof value === 'object' && value?.type === 'new') {

                        formData.append(key, value.file);

                    }
                    else if (typeof value === 'object' && value?.type !== 'new') {

                        formData.append(key, value);

                    }
                    else {

                        formData.append(key, value);

                    }

                });
                const formId = @json($formulario->id);

                fetch(`/formularios/${formId}/validar-registro`, {
                    method: 'POST',
                    body: formData
                })
                    .then(async response => {

                        if (!response.ok) {
                            const err = await response.json();
                            throw err;
                        }

                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {

                            let currentIndex;
                            let isEdit = editIndex !== null;

                            if (isEdit) {
                                currentIndex = editIndex;
                                registros[editIndex] = registro;
                                editIndex = null;
                            } else {
                                registros.push(registro);
                                currentIndex = registros.length - 1;
                            }

                            crearHiddenArchivos(registro, currentIndex, isEdit);

                            alertify.success(data.message);
                            renderTabla();
                            limpiarFormulario();
                        }
                        else {

                            if (!Array.isArray(data.errors) && typeof data.errors === 'object') {

                                let primerError = Object.values(data.errors)[0][0];
                                alertify.error(primerError);
                            }

                            else if (Array.isArray(data.errors)) {

                                alertify.error(data.errors[0]);
                            }

                        }

                    })
                    .catch(() => {
                        alertify.error('Error en el servidor.');
                    });

            }


            function crearHiddenArchivos(registro, index, isEdit = false) {

                let form = document.querySelector('form');
                let container = document.getElementById('hidden_files_container');

                if (!container) {
                    container = document.createElement('div');
                    container.id = 'hidden_files_container';
                    container.style.display = 'none';
                    form.appendChild(container);
                }

                Object.keys(registro).forEach(key => {

                    let value = registro[key];

                    if (typeof value === 'object' && value?.type === 'new') {

                        // 🔹 Si es edición, eliminar el anterior de ese registro/campo
                        if (isEdit) {
                            let oldInput = container.querySelector(
                                `input[name="registros[${index}][${key}]"]`
                            );
                            if (oldInput) oldInput.remove();
                        }

                        // 🔹 Crear nuevo hidden file
                        let input = document.createElement('input');
                        input.type = 'file';
                        input.style.display = 'none';
                        input.name = `registros[${index}][${key}]`;
                        input.files = crearFileList(value.file);

                        container.appendChild(input);
                    }

                });

            }
            function crearFileList(file) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                return dataTransfer.files;
            }
            function actualizarRegistrosJson() {

                // Clonar registros para limpiar archivos (File no se puede serializar)
                let registrosLimpios = registros.map(reg => {

                    let copia = {};

                    Object.keys(reg).forEach(key => {

                        let value = reg[key];

                        // Si es archivo nuevo
                        if (value && typeof value === 'object' && value.preview) {
                            copia[key] = value.preview; // solo guardar preview/base64
                        }

                        // Si es array (checkbox)
                        else if (Array.isArray(value)) {
                            copia[key] = value;
                        }

                        // Normal
                        else {
                            copia[key] = value;
                        }
                    });

                    return copia;
                });

                document.getElementById('registros_json').value =
                    JSON.stringify(registrosLimpios);
            }
            // =============================
            // Render tabla
            // =============================

            function renderTabla() {

                let tbody = document.querySelector('#tabla-registros tbody');
                let thead = document.getElementById('thead-dinamico');

                tbody.innerHTML = '';

                if (registros.length === 0) {
                    thead.innerHTML = `
                                                                                                                                                        <th>#</th>
                                                                                                                                                        <th>Acciones</th>
                                                                                                                                                    `;
                    return;
                }

                // =============================
                // Generar encabezados dinámicos (USANDO LABELS VISIBLES)
                // =============================

                thead.innerHTML = `<th>#</th>`;

                let contenedor = document.getElementById('formulario-dinamico');
                let inputs = contenedor.querySelectorAll('input, select, textarea');

                inputs.forEach(input => {

                    if (!input.name) return;

                    let key = input.name.replace('[]', '');

                    if (thead.querySelector(`[data-key="${key}"]`)) return;

                    let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                    let label = grupo ? grupo.querySelector('label') : null;

                    let textoLabel = label
                        ? label.innerText.replace('*', '').trim()
                        : key;

                    let th = document.createElement('th');
                    th.textContent = textoLabel;
                    th.setAttribute('data-key', key);

                    thead.appendChild(th);
                });

                let thAcciones = document.createElement('th');
                thAcciones.textContent = 'Acciones';
                thead.appendChild(thAcciones);

                // =============================
                // Render filas
                // =============================

                registros.forEach((registro, index) => {

                    let tr = document.createElement('tr');

                    // Número
                    let tdIndex = document.createElement('td');
                    tdIndex.textContent = index + 1;
                    tr.appendChild(tdIndex);

                    thead.querySelectorAll('th[data-key]').forEach(th => {

                        let key = th.getAttribute('data-key');
                        let value = registro[key];

                        let td = document.createElement('td');

                        // 1️⃣ Archivo nuevo
                        if (value && typeof value === 'object' && value.preview) {

                            if (value.file?.type?.startsWith('image')) {

                                td.innerHTML = `
                                                                                                                                                                    <img src="${value.preview}" 
                                                                                                                                                                         style="max-height:60px; border-radius:6px;">
                                                                                                                                                                `;

                            } else if (value.file?.type?.startsWith('video')) {

                                td.innerHTML = `
                                                                                                                                                                    <video src="${value.preview}" 
                                                                                                                                                                           style="max-height:60px;" 
                                                                                                                                                                           controls>
                                                                                                                                                                    </video>
                                                                                                                                                                `;

                            } else {

                                td.innerHTML = `
                                                                                                                                                                    <span class="badge bg-info">
                                                                                                                                                                        Archivo nuevo
                                                                                                                                                                    </span>
                                                                                                                                                                `;
                            }
                        }

                        // 2️⃣ Checkbox (array)
                        else if (Array.isArray(value)) {

                            td.textContent = value.join(', ');
                        }

                        // 3️⃣ Normal
                        else {

                            td.textContent = value ?? '';
                        }

                        tr.appendChild(td);
                    });

                    // =============================
                    // Acciones
                    // =============================

                    let tdAcciones = document.createElement('td');
                    tdAcciones.innerHTML = `
                                                                                                                                                        <button type="button" 
                                                                                                                                                                class="btn btn-sm btn-warning me-2"
                                                                                                                                                                onclick="editarRegistro(${index})">
                                                                                                                                                            Editar
                                                                                                                                                        </button>
                                                                                                                                                        <button type="button" 
                                                                                                                                                                class="btn btn-sm btn-danger"
                                                                                                                                                                onclick="eliminarRegistro(${index})">
                                                                                                                                                            Eliminar
                                                                                                                                                        </button>
                                                                                                                                                    `;

                    tr.appendChild(tdAcciones);
                    tbody.appendChild(tr);
                });
                actualizarRegistrosJson();
            }

            // =============================
            // Editar
            // =============================

            function editarRegistro(index) {

                let registro = registros[index];
                editIndex = index;

                let contenedor = document.getElementById('formulario-dinamico');
                let inputs = contenedor.querySelectorAll('input, select, textarea');

                document.getElementById('btn-agregar-registro').textContent = 'Actualizar registro';

                // =============================
                // Limpiar formulario antes
                // =============================

                inputs.forEach(input => {

                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    } else if (input.type !== 'file') {
                        input.value = '';
                    }

                    // Limpiar previews
                    if (input.dataset.preview) {
                        let previewContainer = document.getElementById(input.dataset.preview);
                        if (previewContainer) previewContainer.innerHTML = '';
                    }

                });

                // =============================
                // Cargar valores del registro
                // =============================

                for (let key in registro) {

                    let value = registro[key];

                    let campo = document.querySelector(`[name="${key}"]`);
                    let campoArray = document.querySelectorAll(`[name="${key}[]"]`);

                    // =============================
                    // 1️⃣ Checkboxes múltiples
                    // =============================

                    if (campoArray.length > 0 && Array.isArray(value)) {

                        campoArray.forEach(el => {
                            el.checked = value.includes(el.value);
                        });

                    }

                    // =============================
                    // 2️⃣ Radio buttons
                    // =============================

                    else if (campo && campo.type === 'radio') {

                        let radios = document.querySelectorAll(`[name="${key}"]`);
                        radios.forEach(radio => {
                            radio.checked = (radio.value === value);
                        });

                    }

                    // =============================
                    // 3️⃣ Archivo NUEVO (con preview)
                    // =============================

                    else if (campo && campo.type === 'file') {

                        if (value && typeof value === 'object' && value.preview) {

                            let previewContainer = document.getElementById(campo.dataset.preview);

                            if (previewContainer) {

                                if (value.file?.type?.startsWith('image')) {

                                    previewContainer.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <img src="${value.preview}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                             style="max-height:150px;border-radius:8px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

                                }
                                else if (value.file?.type?.startsWith('video')) {

                                    previewContainer.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <video src="${value.preview}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                               style="max-height:150px;" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                               controls>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </video>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;

                                }
                                else {

                                    previewContainer.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="alert alert-info p-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            Archivo seleccionado previamente
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                                }
                            }
                        }

                        // Si es archivo existente (string desde BD)
                        else if (typeof value === 'string' && value !== '') {

                            let previewContainer = document.getElementById(campo.dataset.preview);

                            if (previewContainer) {
                                previewContainer.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="alert alert-secondary p-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        Archivo guardado actualmente
                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                            }
                        }

                    }

                    // =============================
                    // 4️⃣ Campo normal
                    // =============================

                    else if (campo) {

                        campo.value = value ?? '';

                    }

                }
            }



            // =============================
            // Eliminar
            // =============================

            function eliminarRegistro(index) {

                registros.splice(index, 1);

                // Reset encabezado si ya no hay registros
                if (registros.length === 0) {
                    document.getElementById('thead-dinamico').innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <th>#</th>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <th>Acciones</th>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;
                }

                renderTabla();
            }


            // =============================
            // Limpiar formulario
            // =============================

            function limpiarFormulario() {

                let contenedor = document.getElementById('formulario-dinamico');
                let elementos = contenedor.querySelectorAll('input, select, textarea');

                elementos.forEach(el => {

                    if (el.type === 'checkbox' || el.type === 'radio') {
                        el.checked = false;
                    }

                    else if (el.type === 'file') {

                        el.value = '';

                        // 🔥 Limpiar preview asociado
                        if (el.dataset.preview) {
                            let previewContainer = document.getElementById(el.dataset.preview);
                            if (previewContainer) {
                                previewContainer.innerHTML = '';
                            }
                        }
                    }


                    else {
                        el.value = '';
                    }

                });
                document.getElementById('btn-agregar-registro').textContent = 'Agregar Registro';

            }
        </script>

    @endif
@endsection