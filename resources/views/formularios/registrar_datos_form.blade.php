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

                            @if($isMobile)

                                <div id="contenedor-cards"></div>

                            @else

                                <div id="contenedor-tabla" class="table-responsive">
                                    <table class="table table-bordered table-striped" id="tabla-registros">
                                        <thead>
                                            <tr id="thead-dinamico">
                                                <th>#</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                            @endif
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
            const AGRUPACION_ACTIVA = @json($formulario->config['agrupacion']['activa'] ?? false);
            const ES_MOBILE = @json($isMobile);

            const CAMPO_INCREMENTO_ID = @json($formulario->config['agrupacion']['campo_incremento'] ?? null);
        </script>

        <script>
            const CAMPOS = @json($formulario->campos->map(fn($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre
            ]));
        </script>

        <script>

            function obtenerNombreCampoIncremento() {

                if (!CAMPO_INCREMENTO_ID) return null;

                const campo = CAMPOS.find(c => c.id == CAMPO_INCREMENTO_ID);
                return campo ? campo.nombre : null;
            }
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

                            const label = contenedor.querySelector(`label[for="${input.id}"]`);
                            const texto = label ? label.innerText.trim() : input.value;

                            registro[key].push({
                                value: input.value,
                                text: texto
                            });
                        }

                    }
                    else if (input.type === 'radio') {

                        if (input.checked) {

                            const label = contenedor.querySelector(`label[for="${input.id}"]`);
                            const texto = label ? label.innerText.trim() : input.value;

                            registro[key] = {
                                value: input.value,
                                text: texto
                            };
                        }

                    }
                    else if (input.tagName === 'SELECT') {

                        const selectedOption = input.options[input.selectedIndex];

                        registro[key] = {
                            value: input.value,
                            text: selectedOption ? selectedOption.text : ''
                        };

                    }
                    else if (input.type === 'file') {

                        if (input.files.length > 0) {

                            let file = input.files[0];

                            registro[key] = {
                                type: 'new',
                                file: file,
                                preview: URL.createObjectURL(file),
                                text: file.name
                            };

                        }
                        else if (editIndex !== null && registros[editIndex]?.[key]) {

                            registro[key] = registros[editIndex][key];

                        }

                    }
                    else {

                        registro[key] = {
                            value: input.value,
                            text: input.value
                        };

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




                // =============================
                // AGRUPACIÓN INTELIGENTE
                // =============================
                if (AGRUPACION_ACTIVA) {

                    const nombreCampoIncremento = obtenerNombreCampoIncremento();

                    if (nombreCampoIncremento && registro[nombreCampoIncremento]) {

                        let indexExistente = registros.findIndex(r => {

                            return Object.keys(registro).every(key => {

                                // Ignorar el campo incremento
                                if (key === nombreCampoIncremento) return true;

                                // Comparar valores (solo value)
                                if (!r[key] || !registro[key]) return false;

                                return r[key].value == registro[key].value;
                            });

                        });

                        if (indexExistente !== -1) {

                            // Incrementar valor
                            let actual = parseFloat(registros[indexExistente][nombreCampoIncremento].value) || 0;
                            let nuevo = parseFloat(registro[nombreCampoIncremento].value) || 0;

                            let suma = actual + nuevo;

                            registros[indexExistente][nombreCampoIncremento] = {
                                value: suma,
                                text: suma
                            };




                            render_informacion();
                            actualizarRegistrosJson();
                            limpiarFormulario();

                            alertify.success('Registro agrupado y cantidad incrementada.');

                            return;
                        }
                    }
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

                    // 1️⃣ Checkbox (array de objetos)
                    if (Array.isArray(value)) {

                        value.forEach(v => {

                            if (typeof v === 'object') {
                                formData.append(key + '[]', v.value ?? '');
                            } else {
                                formData.append(key + '[]', v);
                            }

                        });

                    }

                    // 2️⃣ Archivo nuevo
                    else if (typeof value === 'object' && value?.type === 'new') {

                        formData.append(key, value.file);

                    }

                    // 3️⃣ Objeto normal {value, text}
                    else if (typeof value === 'object') {

                        formData.append(key, value.value ?? '');

                    }

                    // 4️⃣ Valor primitivo (por seguridad)
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
                            render_informacion();
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
                    .catch((error) => {
                        console.error(error);


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

                let registrosLimpios = registros.map(reg => {

                    let copia = {};

                    Object.keys(reg).forEach(key => {

                        let value = reg[key];

                        // 1️⃣ Archivo nuevo
                        if (value && typeof value === 'object' && value.preview) {
                            copia[key] = value.preview;
                        }

                        // 2️⃣ Array (checkbox)
                        else if (Array.isArray(value)) {

                            copia[key] = value.map(item => {

                                if (typeof item === 'object') {
                                    return item.value ?? null;
                                }

                                return item;
                            });

                        }

                        // 3️⃣ Objeto normal {value, text}
                        else if (value && typeof value === 'object') {

                            copia[key] = value.value ?? null;

                        }

                        // 4️⃣ Valor primitivo
                        else {

                            copia[key] = value;

                        }

                    });

                    return copia;
                });

                document.getElementById('registros_json').value =
                    JSON.stringify(registrosLimpios);
            }


            function render_informacion() {

                if (ES_MOBILE) {
                    render_cards();
                } else {
                    render_tabla();
                }

            }

            // =============================
            // Render tabla
            // =============================
            function render_tabla() {

                const tbody = document.querySelector('#tabla-registros tbody');
                const thead = document.getElementById('thead-dinamico');
                const contenedor = document.getElementById('formulario-dinamico');

                tbody.innerHTML = '';

                if (!registros || registros.length === 0) {
                    thead.innerHTML = `
                                                                        <th>#</th>
                                                                        <th>Acciones</th>
                                                                    `;
                    return;
                }

                // =============================
                // 🔥 CAPTURAR CAMPOS UNA SOLA VEZ
                // =============================
                let campos = [];

                contenedor.querySelectorAll('input, select, textarea').forEach(input => {

                    if (!input.name) return;

                    let key = input.name.replace('[]', '');

                    if (campos.some(c => c.key === key)) return;

                    let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                    let label = grupo ? grupo.querySelector('label') : null;

                    let textoLabel = label
                        ? label.innerText.replace('*', '').trim()
                        : key;

                    campos.push({
                        key,
                        label: textoLabel
                    });
                });

                // =============================
                // 🔥 ENCABEZADOS
                // =============================
                thead.innerHTML = `<th>#</th>`;

                campos.forEach(campo => {
                    let th = document.createElement('th');
                    th.textContent = campo.label;
                    th.setAttribute('data-key', campo.key);
                    thead.appendChild(th);
                });

                let thAcciones = document.createElement('th');
                thAcciones.textContent = 'Acciones';
                thead.appendChild(thAcciones);

                // =============================
                // 🔥 FILAS
                // =============================
                registros.forEach((registro, index) => {

                    let tr = document.createElement('tr');

                    // Número
                    let tdIndex = document.createElement('td');
                    tdIndex.textContent = index + 1;
                    tr.appendChild(tdIndex);

                    campos.forEach(campo => {

                        let value = registro[campo.key];
                        let td = document.createElement('td');

                        // 🔥 buscar input SOLO si existe
                        let inputRef = contenedor.querySelector(`[name="${campo.key}"]`);
                        // 🔥 protección contra null
                        td.innerHTML = renderCampoContenido(
                            value,
                            inputRef || null,
                            index,
                            campo.key
                        );

                        tr.appendChild(td);
                    });

                    // =============================
                    // 🔥 ACCIONES
                    // =============================
                    let tdAcciones = document.createElement('td');
                    tdAcciones.innerHTML = `
                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-warning me-2"
                                                                                onclick="editarRegistro(${index})">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>

                                                                        <button type="button" 
                                                                                class="btn btn-sm btn-danger"
                                                                                onclick="eliminarRegistro(${index})">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    `;

                    tr.appendChild(tdAcciones);
                    tbody.appendChild(tr);
                });

                actualizarRegistrosJson();
            }

            function renderCampoContenido(value, input, index, key) {


                if (!input) {

                    if (Array.isArray(value)) {
                        return value.map(v => v.text ?? v.value ?? v).join(', ');
                    }

                    if (typeof value === 'object') {
                        return value.text ?? value.value ?? '';
                    }

                    return value ?? '';
                }

                // 🔥 AUTOCOMPLETADO
                if (input.classList.contains('campo-autocompletado')) {

                    let val = 0;

                    if (typeof value === 'object') {
                        val = parseFloat(value.value ?? 0);
                    } else {
                        val = parseFloat(value ?? 0);
                    }

                    return `
                                                                                                                                                                                                                                                                                <div class="d-flex align-items-center gap-1">

                                                                                                                                                                                                                                                                                    <span class="fw-bold d-flex align-items-center justify-content-center"
                                                                                                                                                                                                                                                                                          style="min-width: 25px; height: 22px;">
                                                                                                                                                                                                                                                                                        ${val}
                                                                                                                                                                                                                                                                                    </span>

                                                                                                                                                                                                                                                                                    <button class="btn btn-sm btn-outline-success p-0 d-flex align-items-center justify-content-center"
                                                                                                                                                                                                                                                                                        style="width:22px; height:22px;"
                                                                                                                                                                                                                                                                                        onclick="cambiarValor(${index}, '${key}', 1)">
                                                                                                                                                                                                                                                                                        +
                                                                                                                                                                                                                                                                                    </button>

                                                                                                                                                                                                                                                                                    ${val > 1 ? `
                                                                                                                                                                                                                                                                                        <button class="btn btn-sm btn-outline-danger p-0 d-flex align-items-center justify-content-center"
                                                                                                                                                                                                                                                                                            style="width:22px; height:22px;"
                                                                                                                                                                                                                                                                                            onclick="cambiarValor(${index}, '${key}', -1)">
                                                                                                                                                                                                                                                                                            -
                                                                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                                    ` : ''}

                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                            `;
                }

                // ARCHIVOS (IMAGEN / VIDEO / OTROS)
                if (value && typeof value === 'object' && value.preview) {

                    // IMAGEN
                    if (value.file?.type?.startsWith('image')) {

                        return `
                                                                                                                                        <a href="${value.preview}" 
                                                                                                                           data-fancybox="gallery"
                                                                                                                           class="ver-link">
                                                                                                                           <i class="fas fa-image"></i> Ver imagen
                                                                                                                        </a>
                                                                                                                                                                    `;

                        // VIDEO
                    } else if (value.file?.type?.startsWith('video')) {

                        return `
                                                                                                                                                <a href="${value.preview}" target="_blank">
                                                                                                                                <i class="fas fa-video"></i> Ver video
                                                                                                                            </a>
                                                                                                                                                                    `;

                        // OTROS ARCHIVOS
                    } else {

                        return `
                                                                                                                                                                        <a href="${value.preview}" 
                                                                                                                                                                           target="_blank">
                                                                                                                                                                           <i class="fas fa-file"></i> Ver archivo
                                                                                                                                                                        </a>
                                                                                                                                                                    `;
                    }
                }

                // ARRAY
                if (Array.isArray(value)) {
                    return value.map(i => i.text ?? i.value).join(', ');
                }

                // OBJETO
                if (value && typeof value === 'object') {
                    return value.text ?? value.value ?? '';
                }

                // 🔥 SIMPLE
                return value ?? '';
            }

            function render_cards() {

                let contenedor = document.getElementById('contenedor-cards');
                contenedor.className = 'row';
                contenedor.innerHTML = '';

                if (!registros || registros.length === 0) {
                    contenedor.innerHTML = `
                                                                                                                                                                                                                                <div class="text-center text-muted py-2">
                                                                                                                                                                                                                                    No hay registros
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            `;
                    return;
                }

                let inputs = document.querySelectorAll('#formulario-dinamico input, #formulario-dinamico select, #formulario-dinamico textarea');

                let campos = [];

                inputs.forEach(input => {

                    if (!input.name) return;

                    let key = input.name.replace('[]', '');

                    if (campos.some(c => c.key === key)) return;

                    let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                    let label = grupo ? grupo.querySelector('label') : null;

                    let textoLabel = label
                        ? label.innerText.replace('*', '').trim()
                        : key;

                    campos.push({
                        key,
                        label: textoLabel,
                        input
                    });
                });

                // =============================
                // 🔥 CARDS COMPACTAS
                // =============================

                registros.forEach((registro, index) => {

                    let contenido = '';

                    campos.forEach(campo => {

                        let value = registro[campo.key];

                        let htmlCampo = renderCampoContenido(
                            value,
                            campo.input,
                            index,
                            campo.key
                        );

                        contenido += `
                                                                                                                                                                                                                                    <div class="col-6 mb-1">
                                                                                                                                                                                                                                        <small class="text-muted d-block" style="font-size:11px;">
                                                                                                                                                                                                                                          <strong>  ${campo.label}</strong> 
                                                                                                                                                                                                                                        </small>
                                                                                                                                                                                                                                        <div style="font-size:13px; line-height:1.2;">
                                                                                                                                                                                                                                            ${htmlCampo}
                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                `;
                    });

                    let card = document.createElement('div');
                    card.className = 'card mb-2 shadow-sm border-0';

                    card.innerHTML = `
                                                                                                                                                                                                                                <div class="card-body p-2">

                                                                                                                                                                                                                                    <!-- HEADER -->
                                                                                                                                                                                                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                                                                                                                                                                                                        <span class="badge bg-secondary" style="font-size:11px;">
                                                                                                                                                                                                                                            #${index + 1}
                                                                                                                                                                                                                                        </span>

                                                                                                                                                                                                                                        <div class="d-flex gap-1">
                                                                                                                                                                                                                                        <button type='button' class="btn btn-xs btn-warning p-1 px-2"
                                                                                                                                                                                                                                            title="Editar"
                                                                                                                                                                                                                                            onclick="editarRegistro(${index})">
                                                                                                                                                                                                                                            <i class="fas fa-edit"></i>
                                                                                                                                                                                                                                        </button>

                                                                                                                                                                                                                                        <button type='button'  class="btn btn-xs btn-danger p-1 px-2"
                                                                                                                                                                                                                                            title="Eliminar"
                                                                                                                                                                                                                                            onclick="eliminarRegistro(${index})">
                                                                                                                                                                                                                                            <i class="fas fa-trash"></i>
                                                                                                                                                                                                                                        </button>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                    <!-- CONTENIDO -->
                                                                                                                                                                                                                                    <div class="row gx-2">
                                                                                                                                                                                                                                        ${contenido}
                                                                                                                                                                                                                                    </div>

                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            `;
                    let col = document.createElement('div');
                    col.className = 'col-12 col-sm-6 col-md-4 col-lg-3 mb-2';

                    col.appendChild(card);
                    contenedor.appendChild(col);
                });

                actualizarRegistrosJson();
            }

            function cambiarValor(index, key, cambio) {

                let registro = registros[index];

                if (!registro[key]) return;

                let actual = 0;

                if (typeof registro[key] === 'object') {
                    actual = parseFloat(registro[key].value ?? 0);
                } else {
                    actual = parseFloat(registro[key] ?? 0);
                }

                let nuevo = actual + cambio;

                // mínimo 0
                if (nuevo < 0) nuevo = 0;

                registro[key] = {
                    value: nuevo,
                    text: nuevo
                };

                render_informacion();
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
                // 1️⃣ LIMPIAR FORMULARIO
                // =============================
                inputs.forEach(input => {

                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                    }
                    else if (input.type !== 'file') {
                        input.value = '';
                    }

                    // Limpiar preview
                    if (input.dataset.preview) {
                        let preview = document.getElementById(input.dataset.preview);
                        if (preview) preview.innerHTML = '';
                    }

                    // Reset TomSelect
                    if (input.classList.contains('tom-select') && input.tomselect) {
                        input.tomselect.clear();
                    }

                });

                // =============================
                // 2️⃣ CARGAR DATOS
                // =============================
                for (let key in registro) {

                    let value = registro[key];

                    let campo = contenedor.querySelector(`[name="${key}"]`);
                    let campoArray = contenedor.querySelectorAll(`[name="${key}[]"]`);

                    // =============================
                    // CHECKBOX
                    // =============================
                    if (campoArray.length > 0 && Array.isArray(value)) {

                        campoArray.forEach(el => {
                            el.checked = value.some(v => {
                                if (typeof v === 'object') {
                                    return v.value == el.value;
                                }
                                return v == el.value;
                            });
                        });

                    }

                    // =============================
                    // RADIO
                    // =============================
                    else if (campo && campo.type === 'radio') {

                        let radios = contenedor.querySelectorAll(`[name="${key}"]`);
                        radios.forEach(radio => {
                            radio.checked = (radio.value == value);
                        });

                    }

                    // =============================
                    // FILE / PREVIEW
                    // =============================
                    else if (campo && campo.type === 'file') {

                        let preview = document.getElementById(campo.dataset.preview);

                        if (!preview) return;

                        // archivo nuevo (temporal)
                        if (value && typeof value === 'object' && value.preview) {

                            if (value.file?.type?.startsWith('image')) {

                                preview.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="${value.preview}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                 style="max-height:150px;border-radius:8px;">
                                                                                                                                                                                                                                                                                                                                                                                                                        `;

                            } else if (value.file?.type?.startsWith('video')) {

                                preview.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                            <video src="${value.preview}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                   style="max-height:150px;" 
                                                                                                                                                                                                                                                                                                                                                                                                                                   controls></video>
                                                                                                                                                                                                                                                                                                                                                                                                                        `;

                            } else {

                                preview.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="alert alert-info p-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                Archivo seleccionado previamente
                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                        `;
                            }

                        }

                        // archivo ya guardado
                        else if (typeof value === 'string' && value !== '') {

                            preview.innerHTML = `
                                                                                                                                                                                                                                                                                                                                                                                                                        <div class="alert alert-secondary p-2">
                                                                                                                                                                                                                                                                                                                                                                                                                            Archivo guardado actualmente
                                                                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                                                    `;
                        }

                    }

                    // =============================
                    // SELECT + TOMSELECT 🔥
                    // =============================
                    else if (campo && campo.tagName === 'SELECT') {

                        if (campo.classList.contains('tom-select') && campo.tomselect) {

                            let val = value;

                            // Si viene como objeto 🔥
                            if (typeof value === 'object' && value !== null) {
                                val = value.value ?? value.id ?? value.codigo ?? '';
                            }

                            // Si no existe la opción → crearla
                            if (val && !campo.querySelector(`option[value="${val}"]`)) {

                                let texto = value.text ?? value.label ?? val;

                                campo.tomselect.addOption({
                                    value: val,
                                    text: texto
                                });

                            }

                            campo.tomselect.setValue(val);

                        } else {

                            campo.value = value ?? '';
                        }

                        // Disparar relación
                        campo.dispatchEvent(new Event('change', { bubbles: true }));
                    }


                    else if (campo && campo.type === 'hidden' && campo.classList.contains('campo-autocompletado')) {


                        let base = campo.dataset.default ?? campo.value ?? 0;

                        campo.value = base;

                    }

                    else if (campo) {

                        let val = getValorPlano(value);

                        switch (campo.type) {

                            case 'date':
                                // ⚠️ Asegurar formato YYYY-MM-DD
                                if (val && val.includes('/')) {
                                    let partes = val.split('/');
                                    val = `${partes[2]}-${partes[1]}-${partes[0]}`;
                                }
                                campo.value = val;
                                break;

                            case 'number':
                                campo.value = parseFloat(val) || '';
                                break;

                            case 'color':
                                campo.value = val || '#000000';
                                break;

                            case 'password':
                                campo.value = ''; // 🔐 nunca rellenar passwords
                                break;

                            default:
                                campo.value = val;
                        }

                    }

                }

            }

            function getValorPlano(val) {

                if (Array.isArray(val)) {
                    return val.map(v => getValorPlano(v)).join(', ');
                }

                if (val && typeof val === 'object') {
                    return val.value ?? val.text ?? val.label ?? val.id ?? '';
                }

                return val ?? '';
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

                render_informacion();
            }


            // =============================
            // Limpiar formulario
            // =============================

            function limpiarFormulario() {
                /*
                                let contenedor = document.getElementById('formulario-dinamico');
                                let elementos = contenedor.querySelectorAll('input, select, textarea');

                                elementos.forEach(el => {

                                    // 🔥 NO limpiar hidden autocompletado
                                    if (el.type === 'hidden' && el.classList.contains('campo-autocompletado')) {
                                        return;
                                    }

                                    if (el.type === 'checkbox' || el.type === 'radio') {
                                        el.checked = false;
                                    }

                                    else if (el.type === 'file') {

                                        el.value = '';

                                        // Limpiar preview asociado
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

                                });*/

                document.getElementById('btn-agregar-registro').textContent = 'Agregar Registro';
            }
        </script>

    @endif
@endsection