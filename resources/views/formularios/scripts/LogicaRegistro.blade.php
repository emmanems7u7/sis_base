@if (isset($formulario->config['registro_multiple']) && $formulario->config['registro_multiple'])
    <script>
        const AGRUPACION_ACTIVA = @json($formulario->config['agrupacion']['activa'] ?? false);
        const ES_MOBILE = @json($isMobile);

        const CAMPO_INCREMENTO_ID = @json($formulario->config['agrupacion']['campo_incremento'] ?? null);
    </script>

    <script>
        const CAMPOS = @json(
            $formulario->campos->map(fn($c) => [
                    'id' => $c->id,
                    'nombre' => $c->nombre,
                ]));
        const FORMULAS = JSON.parse('{!! addslashes(json_encode($formulas)) !!}');
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            let oldData = document.getElementById('registros_json').value;

            if (oldData) {

                registros = JSON.parse(oldData).map(normalizarRegistro);

                registros.forEach((_, index) => {
                    ejecutarFormulas(registros, FORMULAS, index);
                });

            }
            actualizarRegistrosJson();

            render_informacion();

        });

        function normalizarRegistro(reg) {
            let nuevo = {};

            Object.keys(reg).forEach(key => {

                let val = reg[key];

                // Si ya es objeto, lo dejamos
                if (typeof val === 'object' && val !== null && 'value' in val) {
                    nuevo[key] = val;
                } else {
                    nuevo[key] = {
                        value: val,
                        text: val
                    };
                }

            });

            return nuevo;
        }

        function obtenerNombreCampoIncremento() {
            if (!CAMPO_INCREMENTO_ID) return null;

            const campo = CAMPOS.find(c => c.id == CAMPO_INCREMENTO_ID);
            const FORM_ID = {{ $formulario->id }};
            // Verifica que exista campo
            if (!campo) return null;

            // Construye la clave completa como viene en tus inputs
            return `form_${FORM_ID}[${campo.id}]`;
        }
        let registros = [];
        let editIndex = null;

        document.getElementById('btn-agregar-registro').addEventListener('click', function() {

            let contenedor = document.getElementById('formulario-dinamico');
            let inputs = contenedor.querySelectorAll('input, select, textarea');

            let registro = {};
            let tieneError = false;
            let primerError = null;

            const btnAgregar = document.getElementById('btn-agregar-registro');

            const texto = btnAgregar.querySelector('.btn-text');

            if (!btnAgregar.dataset.originalText) {
                btnAgregar.dataset.originalText = texto.textContent;
            }

            // Limpiar errores previos

            inputs.forEach(input => {
                input.classList.remove('is-invalid');
            });

            inputs.forEach(input => {

                if (!input.name) return;

                let key = input.name.replace('[]', '');


                // Obtener nombre visible desde el LABEL

                let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                let label = grupo ? grupo.querySelector('label') : null;

                let nombreCampo = label ?
                    label.innerText.replace('*', '').trim() :
                    'Este campo';


                // VALIDACIÓN REQUERIDOS

                if (input.required) {

                    if (input.type === 'checkbox') {

                        let grupoChecks = contenedor.querySelectorAll(`[name="${input.name}"]:checked`);
                        if (grupoChecks.length === 0) {
                            tieneError = true;
                            input.classList.add('is-invalid');
                            if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                        }

                    } else if (input.type === 'radio') {

                        let grupoRadios = contenedor.querySelectorAll(`[name="${input.name}"]:checked`);
                        if (grupoRadios.length === 0) {
                            tieneError = true;
                            if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                        }

                    } else if (input.type === 'file') {

                        if (
                            input.files.length === 0 &&
                            !(editIndex !== null && registros[editIndex]?.[key])
                        ) {
                            tieneError = true;
                            input.classList.add('is-invalid');
                            if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                        }

                    } else {

                        if (!input.value.trim()) {
                            tieneError = true;
                            input.classList.add('is-invalid');
                            if (!primerError) primerError = `El campo ${nombreCampo} es obligatorio.`;
                        }

                    }
                }


                // CONSTRUCCIÓN DEL REGISTRO


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

                } else if (input.type === 'radio') {

                    if (input.checked) {

                        let label = contenedor.querySelector(`label[for="radio_${input.value}"]`);

                        const texto = label ? label.innerText.trim() : input.value;

                        registro[key] = {
                            value: input.value,
                            text: texto
                        };
                    }
                } else if (input.tagName === 'SELECT') {

                    const selectedOption = input.options[input.selectedIndex];

                    registro[key] = {
                        value: input.value,
                        text: selectedOption ? selectedOption.text : ''
                    };

                } else if (input.type === 'file') {

                    if (input.files.length > 0) {

                        let file = input.files[0];

                        registro[key] = {
                            type: 'new',
                            file: file,
                            preview: URL.createObjectURL(file),
                            text: file.name
                        };

                    } else if (editIndex !== null && registros[editIndex]?.[key]) {

                        registro[key] = registros[editIndex][key];

                    }

                } else {

                    registro[key] = {
                        value: input.value,
                        text: input.value
                    };

                }
            });


            if (tieneError) {

                mostrarAlerta('warning', primerError ?? 'Complete los campos obligatorios.');
                let campoInvalido = contenedor.querySelector('.is-invalid');
                if (campoInvalido) {
                    campoInvalido.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    campoInvalido.focus();
                }

                return;
            }

            if (!tieneError) {

                ejecutarFormulasDinamicas([registro], FORMULAS);

            }


            const camposCalculados = new Set();

            FORMULAS.forEach(f => {
                const key = `form_${f.destino.form}[${f.destino.campo_id}]`;
                camposCalculados.add(key);
            });

            // AGRUPACIÓN INTELIGENTE

            if (AGRUPACION_ACTIVA) {
                const nombreCampoIncremento = obtenerNombreCampoIncremento();


                if (nombreCampoIncremento && registro[nombreCampoIncremento]) {


                    let indexExistente = registros.findIndex(r => {

                        return Object.keys(registro).every(key => {

                            if (key === nombreCampoIncremento) return true;

                            if (camposCalculados.has(key)) return true;

                            if (!r[key] || !registro[key]) return false;

                            let val1 = String(r[key].value).trim();
                            let val2 = String(registro[key].value).trim();

                            return val1 === val2;
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


                        ejecutarFormulas(registros, FORMULAS, indexExistente)
                        render_informacion();
                        actualizarRegistrosJson();
                        const btnAgregar = document.getElementById('btn-agregar-registro');

                        const texto = btnAgregar.querySelector('.btn-text');

                        if (!btnAgregar.dataset.originalText) {
                            btnAgregar.dataset.originalText = texto.textContent;
                        }
                        limpiarFormulario();
                        mostrarAlerta('success', 'Registro agrupado y cantidad incrementada.');

                        return;
                    }
                }
            }

            validacion(registro);


        });

        function ObtenerInfo(registro) {
            let formData = new FormData();

            formData.append('_token', document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
            );

            Object.keys(registro).forEach(key => {

                let value = registro[key];

                //Checkbox (array de objetos)
                if (Array.isArray(value)) {

                    value.forEach(v => {

                        if (typeof v === 'object') {
                            formData.append(key + '[]', v.value ?? '');
                        } else {
                            formData.append(key + '[]', v);
                        }

                    });

                }

                // Archivo nuevo
                else if (typeof value === 'object' && value?.type === 'new') {

                    formData.append(key, value.file);

                }

                //  Objeto normal {value, text}
                else if (typeof value === 'object') {

                    formData.append(key, value.value ?? '');

                }

                // Valor primitivo 
                else {

                    formData.append(key, value);

                }

            });
            return formData;
        }

        function validacion(registro) {

            let contenedor = document.getElementById('formulario-dinamico');

            let formData = new FormData();
            formData = ObtenerInfo(registro);

            const formId = @json($formulario->id);

            fetch(`/formularios/validar-registro`, {
                    method: 'POST',
                    body: formData,
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
                        mostrarAlerta('success', data.message);
                        ejecutarFormulas(registros, FORMULAS, currentIndex)

                        render_informacion();

                        limpiarFormulario();

                        const btnAgregar = document.getElementById('btn-agregar-registro');

                        const texto = btnAgregar.querySelector('.btn-text');
                        console.log(texto);
                        if (!btnAgregar.dataset.originalText) {
                            btnAgregar.dataset.originalText = texto.textContent;
                        }

                    } else {

                        if (!Array.isArray(data.errors) && typeof data.errors === 'object') {

                            let primerError = Object.values(data.errors)[0][0];
                            mostrarAlerta('error', primerError);
                        } else if (Array.isArray(data.errors)) {
                            mostrarAlerta('error', data.errors[0]);

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

                    //Si es edición, eliminar el anterior de ese registro/campo
                    if (isEdit) {
                        let oldInput = container.querySelector(
                            `input[name="registros[${index}][${key}]"]`
                        );
                        if (oldInput) oldInput.remove();
                    }

                    //Crear nuevo hidden file
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



            document.getElementById('registros_json').value =
                JSON.stringify(registros);
        }


        function render_informacion() {

            if (ES_MOBILE) {
                render_cards();
            } else {
                render_tabla();
            }

        }
        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function obtenerValor(registro, form, nombre) {

            const formKey = `form_${form}[${nombre}]`;

            let valor = registro?.[formKey]?.value;


            // FALLBACK DOM
            if (valor === undefined) {

                const input = document.querySelector(`input[name="${formKey}"]`);

                valor = input ? input.value : '';

            }

            return valor;

        }

        function obtenerValorNumerico(registro, form, nombre) {

            const valorRaw = obtenerValor(registro, form, nombre);

            const valor = parseFloat(valorRaw);

            return !isNaN(valor) ?
                valor :
                0;

        }

        function aplicarOperacion(resultado, operador, valor) {

            // CONCATENACION
            if (
                typeof resultado === 'string' ||
                typeof valor === 'string'
            ) {

                if (operador === '+') {

                    return String(resultado ?? '') + String(valor ?? '');

                }

                return resultado;

            }

            switch (operador) {

                case '+':
                    return resultado + valor;

                case '-':
                    return resultado - valor;

                case '*':
                    return resultado * valor;

                case '/':
                    return valor != 0 ?
                        resultado / valor :
                        resultado;

                default:
                    return resultado;

            }

        }

        function resolverFormula(registro, formula) {

            let resultado = null;
            let operador = null;

            formula.forEach(item => {

                /*
                |--------------------------------------------------------------------------
                | OPERADOR
                |--------------------------------------------------------------------------
                */
                if (item.tipo === 'operador') {

                    if (item.valor === '=') return;

                    operador = item.valor;

                    return;

                }

                /*
                |--------------------------------------------------------------------------
                | CAMPO
                |--------------------------------------------------------------------------
                */
                if (item.tipo === 'campo') {

                    let valorRaw = obtenerValor(
                        registro,
                        item.form,
                        item.campo_id
                    );

                    let valor;

                    if (
                        !isNaN(valorRaw) &&
                        valorRaw !== '' &&
                        valorRaw !== null
                    ) {

                        valor = parseFloat(valorRaw);

                    } else {

                        valor = valorRaw;

                    }

                    // PRIMER VALOR
                    if (resultado === null) {

                        resultado = valor;

                        return;

                    }

                    // OPERACION
                    if (operador) {

                        resultado = aplicarOperacion(
                            resultado,
                            operador,
                            valor
                        );

                        operador = null;

                    }

                }

            });

            return resultado ?? 0;

        }

        function resolverFuncion(registros, formula) {

            const funcion = formula.find(x => x.tipo === 'funcion');

            const campo = formula.find(x => x.tipo === 'campo');

            if (!funcion || !campo) {
                return 0;
            }

            const valores = [];
            registros.forEach(reg => {

                const valor = obtenerValorNumerico(
                    reg,
                    campo.form,
                    campo.campo_id
                );
                if (!isNaN(valor)) {
                    valores.push(valor);
                }

            });

            switch (funcion.nombre) {

                case 'SUM':

                    return valores.reduce((a, b) => a + b, 0);

                case 'AVG':

                    return valores.length ?
                        valores.reduce((a, b) => a + b, 0) / valores.length :
                        0;

                case 'MIN':

                    return valores.length ?
                        Math.min(...valores) :
                        0;

                case 'MAX':

                    return valores.length ?
                        Math.max(...valores) :
                        0;

                case 'COUNT':

                    return valores.length;

                default:

                    return 0;

            }

        }

        function asignarResultado(registro, destinoForm, total, dispararEventos = false) {

            if (total === null || total === undefined) {
                return;
            }

            // REGISTRO
            if (registro) {

                registro[destinoForm] = {
                    value: total,
                    text: total
                };

            }

            // DOM
            const input = document.querySelector(`input[name="${destinoForm}"]`);

            if (input) {

                input.value = total;

                if (dispararEventos) {

                    input.dispatchEvent(new Event('input'));

                    input.dispatchEvent(new Event('change'));

                }

            }

        }

        /*
        |--------------------------------------------------------------------------
        | FORMULAS NORMALES
        |--------------------------------------------------------------------------
        */

        function ejecutarFormulas(registros, formulas, index) {
            if (!Array.isArray(registros) || !Array.isArray(formulas)) {
                return;
            }

            formulas.forEach(f => {

                const destinoForm =
                    `form_${f.destino.form}[${f.destino.campo_id}]`;

                let total = null;

                /*
                |--------------------------------------------------------------------------
                | MODO ASIGNACION
                |--------------------------------------------------------------------------
                */
                if (f.modo === 'asignacion') {

                    const valorActual =
                        registros[index]?.[destinoForm]?.value;

                    if (
                        valorActual !== undefined &&
                        valorActual !== null &&
                        valorActual !== ''
                    ) {
                        return;
                    }

                    const campo = f.formula.find(x => x.tipo === 'campo');

                    if (!campo) return;

                    total = obtenerValor(
                        registros[index],
                        campo.form,
                        campo.campo_id
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | MODO CALCULO
                |--------------------------------------------------------------------------
                */
                else if (f.modo === 'calculo') {

                    /*
                    |--------------------------------------------------------------------------
                    | CALCULO POR FILA
                    |--------------------------------------------------------------------------
                    */
                    if (f.tipo_calculo === 'fila') {

                        total = resolverFormula(
                            registros[index],
                            f.formula
                        );

                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CALCULO GLOBAL
                    |--------------------------------------------------------------------------
                    */
                    else if (f.tipo_calculo === 'global') {

                        total = 0;

                        registros.forEach(reg => {

                            total += resolverFormula(
                                reg,
                                f.formula
                            );

                        });

                    }

                }

                /*
                |--------------------------------------------------------------------------
                | MODO FUNCION
                |--------------------------------------------------------------------------
                */
                else if (f.modo === 'funcion') {

                    total = resolverFuncion(
                        registros,
                        f.formula
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | ASIGNAR RESULTADO
                |--------------------------------------------------------------------------
                */
                asignarResultado(
                    registros[index],
                    destinoForm,
                    total
                );

            });

        }

        /*
        |--------------------------------------------------------------------------
        | FORMULAS DINAMICAS
        |--------------------------------------------------------------------------
        */

        function ejecutarFormulasDinamicas(registros, formulas) {

            if (!Array.isArray(registros) || !Array.isArray(formulas)) {
                return;
            }

            const registro = registros[0];

            if (!registro) return;

            const formActual = Object.keys(registro)[0]
                ?.match(/form_(\d+)/)?.[1];

            formulas.forEach(f => {

                /*
                |--------------------------------------------------------------------------
                | SOLO FORM DEL REGISTRO ACTUAL
                |--------------------------------------------------------------------------
                */
                if (
                    String(f.destino.form) !== String(formActual)
                ) {
                    return;
                }

                const destinoForm =
                    `form_${f.destino.form}[${f.destino.campo_id}]`;

                let total = null;

                /*
                |--------------------------------------------------------------------------
                | MODO ASIGNACION
                |--------------------------------------------------------------------------
                */
                if (f.modo === 'asignacion') {

                    const campo = f.formula.find(x => x.tipo === 'campo');
                    if (!campo) return;

                    total = obtenerValor(
                        registro,
                        campo.form,
                        campo.campo_id
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | MODO CALCULO
                |--------------------------------------------------------------------------
                */
                else if (f.modo === 'calculo') {

                    total = resolverFormula(
                        registro,
                        f.formula
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | MODO FUNCION
                |--------------------------------------------------------------------------
                */
                else if (f.modo === 'funcion') {

                    total = resolverFuncion(
                        [registro],
                        f.formula
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | ASIGNAR RESULTADO
                |--------------------------------------------------------------------------
                */
                asignarResultado(
                    registro,
                    destinoForm,
                    total,
                    true
                );

            });

        }

        function render_tabla() {

            const tbody = document.querySelector('#tabla-registros tbody');
            const thead = document.getElementById('thead-dinamico');
            const contenedor = document.getElementById('formulario-dinamico');

            tbody.innerHTML = '';

            if (!registros || registros.length === 0) {
                thead.innerHTML =
                    `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <th>#</th>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <th>Acciones</th>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                `;
                return;
            }


            //  CAPTURAR CAMPOS UNA SOLA VEZ

            let campos = [];

            contenedor.querySelectorAll('input, select, textarea').forEach(input => {

                if (!input.name) return;

                let key = input.name.replace('[]', '');
                let key_visible = input.dataset.etiqueta;

                if (campos.some(c => c.key === key)) return;

                let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                let label = grupo ? grupo.querySelector('label') : null;

                let textoLabel = input.dataset.etiqueta;

                campos.push({
                    key,
                    key_visible,
                    label: textoLabel
                });
            });

            // ENCABEZADOS

            thead.innerHTML = `<th>#</th>`;

            campos.forEach(campo => {
                let th = document.createElement('th');
                th.textContent = campo.key_visible;
                th.setAttribute('data-key', campo.key);
                thead.appendChild(th);
            });

            let thAcciones = document.createElement('th');
            thAcciones.textContent = 'Acciones';
            thead.appendChild(thAcciones);


            //  FILAS

            registros.forEach((registro, index) => {

                let tr = document.createElement('tr');

                // Número
                let tdIndex = document.createElement('td');
                tdIndex.textContent = index + 1;
                tr.appendChild(tdIndex);

                campos.forEach(campo => {

                    let value = registro[campo.key];


                    let td = document.createElement('td');

                    //  buscar input SOLO si existe
                    let inputRef = contenedor.querySelector(`[name="${campo.key}"]`);
                    //  protección contra null
                    td.innerHTML = renderCampoContenido(
                        value,
                        inputRef || null,
                        index,
                        campo.key
                    );

                    tr.appendChild(td);
                });


                //  ACCIONES

                let tdAcciones = document.createElement('td');
                tdAcciones.innerHTML =
                    `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            class="btn btn-sm btn-warning me-2"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            onclick="editarRegistro(${index})">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <i class="fas fa-edit"></i>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </button>

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <button type="button" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            class="btn btn-sm btn-danger"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            onclick="confirmarEliminarRegistro(${index})">
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

            //  AUTOCOMPLETADO
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

            //  SIMPLE
            return value ?? '';
        }

        function render_cards() {

            let contenedor = document.getElementById('contenedor-cards');
            contenedor.className = 'row';
            contenedor.innerHTML = '';

            if (!registros || registros.length === 0) {
                contenedor.innerHTML =
                    `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <div class="text-center text-muted py-2">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                No hay registros
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        `;
                return;
            }

            let contenedorForm = document.getElementById('formulario-dinamico');
            let inputs = contenedorForm.querySelectorAll('input, select, textarea');
            let campos = [];

            inputs.forEach(input => {

                if (!input.name) return;

                let key = input.name.replace('[]', '');
                let key_visible = input.dataset.etiqueta;
                if (campos.some(c => c.key === key)) return;

                let grupo = input.closest('.mb-3, .form-group, .col-md-6, .col-md-12');
                let label = grupo ? grupo.querySelector('label') : null;

                let textoLabel = label ?
                    label.innerText.replace('*', '').trim() :
                    key;

                campos.push({
                    key,
                    label: textoLabel,
                    key_visible,
                    input
                });
            });





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

                    contenido +=
                        `
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <div class="col-6 mb-1">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <small class="text-muted d-block" style="font-size:11px;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      <strong>  ${campo.key_visible}</strong> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </small>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <div style="font-size:13px; line-height:1.2;">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        ${htmlCampo}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            `;
                });

                let card = document.createElement('div');
                card.className = 'card mb-2 shadow-sm border-0';

                card.innerHTML =
                    `
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
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        onclick="confirmarEliminarRegistro(${index})">
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

            if (nuevo < 0) nuevo = 0;

            registro[key] = {
                value: nuevo,
                text: nuevo
            };
            ejecutarFormulas(registros, FORMULAS, index)
            render_informacion();
        }


        // Editar

        function editarRegistro(index) {

            let registro = registros[index];
            editIndex = index;

            let contenedor = document.getElementById('formulario-dinamico');
            let inputs = contenedor.querySelectorAll('input, select, textarea');
            const btnAgregar = document.getElementById('btn-agregar-registro');
            const texto = btnAgregar.querySelector('.btn-text');
            if (!btnAgregar.dataset.originalText) {
                btnAgregar.dataset.originalText = texto.textContent;
            }

            texto.textContent = 'Actualizar registro';
            //  LIMPIAR FORMULARIO

            inputs.forEach(input => {

                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.type !== 'file') {
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


            //  CARGAR DATOS




            for (let key in registro) {

                let value = registro[key];

                let campos = contenedor.querySelectorAll(`[name="${key}"], [name="${key}[]"]`);
                if (campos.length === 0) continue;

                let campo = campos[0];
                let tipo = campo.dataset.tipo;


                // CHECKBOX

                if (tipo === 'checkbox') {

                    campos.forEach(el => {
                        el.checked = value.some(v =>
                            (typeof v === 'object' ? v.value : v) == el.value
                        );
                    });
                }


                // RADIO
                else if (tipo === 'radio') {

                    campos.forEach(radio => {
                        let val = typeof value === 'object' ? value.value : value;
                        radio.checked = (radio.value == val);
                    });
                } else if (tipo === 'selector' || tipo === 'campo_relacion') {

                    let val = value;

                    if (typeof value === 'object') {
                        val = value.value ?? value.id ?? value.codigo ?? '';
                    }

                    if (campo.tomselect) {

                        if (val && !campo.querySelector(`option[value="${val}"]`)) {

                            campo.tomselect.addOption({
                                value: val,
                                text: value.text ?? value.label ?? val
                            });
                        }

                        campo.tomselect.setValue(val);

                    } else {
                        campo.value = val;
                    }

                    campo.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                }


                // ARCHIVOS (archivo, imagen, video)
                else if (['archivo', 'imagen', 'video'].includes(tipo)) {

                    let preview = null;


                    if (campo.dataset.preview) {
                        preview = document.getElementById(campo.dataset.preview);
                    }


                    if (!preview) {
                        preview = campo.closest('.form-group, .mb-3, div')?.querySelector('.preview-archivo');
                    }


                    if (!preview) {
                        preview = document.createElement('div');
                        preview.classList.add('preview-archivo', 'mt-2');
                        campo.parentNode.insertBefore(preview, campo);
                    }


                    preview.innerHTML = '';


                    // ARCHIVO NUEVO 

                    if (value && typeof value === 'object' && value.preview) {

                        if (value.file?.type?.startsWith('image')) {

                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                                <a href="${value.preview}" 
                                                                                                                                                                                                                                                                                                                                                                                data-fancybox="imagenes"
                                                                                                                                                                                                                                                                                                                                                                                data-caption="Imagen seleccionada"
                                                                                                                                                                                                                                                                                                                                                                                class="ver-link">
                                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-image"></i> Ver imagen
                                                                                                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                                                                                            `;
                            if (typeof Fancybox !== 'undefined') {
                                Fancybox.bind('[data-fancybox="imagenes"]');
                            }

                        } else if (value.file?.type?.startsWith('video')) {

                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                <a href="${value.preview}" target="_blank" class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-video"></i> Ver video
                                                                                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                                                                            `;

                        } else {

                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                <a href="${value.preview}" target="_blank" class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-file"></i> Ver archivo
                                                                                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                                                                            `;
                        }
                    }


                    // ARCHIVO YA GUARDADO
                    else if (typeof value === 'string' && value !== '') {

                        let baseUrl = `/archivos/formulario_${FORM_ID}`; // asegúrate de tener esto global

                        let url = '';

                        if (tipo === 'imagen') {

                            url = `${baseUrl}/imagenes/${value}`;

                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                        <a href="${url}" 
                                                                                                                                                                                                                                                                                                                                                                        data-fancybox="imagenes_${key}" 
                                                                                                                                                                                                                                                                                                                                                                        data-caption="Imagen"
                                                                                                                                                                                                                                                                                                                                                                        class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                            <i class="fas fa-image"></i> Ver imagen
                                                                                                                                                                                                                                                                                                                                                                        </a>
                                                                                                                                                                                                                                                                                                                                                                    `;

                            if (typeof Fancybox !== 'undefined') {
                                Fancybox.bind(`[data-fancybox="imagenes_${key}"]`);
                            }
                        } else if (tipo === 'video') {
                            url = `${baseUrl}/videos/${value}`;
                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                <a href="${url}" target="_blank" class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-video"></i> Ver video
                                                                                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                                                                            `;
                        } else {
                            url = `${baseUrl}/archivos/${value}`;
                            preview.innerHTML =
                                `
                                                                                                                                                                                                                                                                                                                                                                <a href="${url}" target="_blank" class="text-primary">
                                                                                                                                                                                                                                                                                                                                                                    <i class="fas fa-file"></i> Ver archivo
                                                                                                                                                                                                                                                                                                                                                                </a>
                                                                                                                                                                                                                                                                                                                                                            `;
                        }
                    }
                }



                // FECHA
                else if (tipo === 'fecha') {

                    let val = getValorPlano(value);

                    if (val && val.includes('/')) {
                        let partes = val.split('/');
                        val = `${partes[2]}-${partes[1]}-${partes[0]}`;
                    }

                    campo.value = val;
                }


                // HORA
                else if (tipo === 'hora') {

                    campo.value = getValorPlano(value);
                }


                // COLOR
                else if (tipo === 'color') {

                    campo.value = value || '#000000';
                }


                // AUTOCOMPLETADO
                else if (tipo === 'campo autocompletado') {

                    campo.value = campo.dataset.default ?? campo.value ?? '';
                }


                // TEXTAREA
                else if (tipo === 'textarea') {

                    campo.value = getValorPlano(value);
                }


                // INPUTS BÁSICOS
                else {

                    let val = getValorPlano(value);

                    switch (tipo) {

                        case 'number':
                            campo.value = parseFloat(val) || '';
                            break;

                        case 'password':
                            campo.value = '';
                            break;

                        default:
                            campo.value = val;
                    }
                }
            }
            mostrarAlerta('success', 'Esta Editando el registro #' + (index + 1));
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


        // Eliminar

        function confirmarEliminarRegistro(index) {

            mostrarAlerta('confirm', '¿Estás seguro de que deseas eliminar este registro?', {
                titulo: 'Confirmar eliminación',
                onOk: () => eliminarRegistro(index),
                onCancel: () => alertify.error('Acción cancelada')
            });
        }

        function eliminarRegistro(index) {

            const registro = registros[index];


            let contenedor = document.getElementById('formulario-dinamico');

            let formData = new FormData();
            formData = ObtenerInfo(registro);

            const formId = @json($formulario->id);
            @if ($edit)
                fetch(`/formularios/${formId}/eliminar-registro`, {
                        method: 'POST',
                        body: formData,
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

                            registros.splice(index, 1);

                            const thead = document.getElementById('thead-dinamico');

                            if (registros.length === 0 && thead) {
                                thead.innerHTML = `<th>#</th><th>Acciones</th>`;
                            }

                            ejecutarFormulas(registros, FORMULAS, index);
                            render_informacion();
                        } else {

                            if (data.message) {
                                mostrarAlerta('error', data.message);

                            }
                        }

                    })
                    .catch((error) => {
                        console.error(error);


                    });
            @else
                registros.splice(index, 1);

                const thead = document.getElementById('thead-dinamico');

                if (registros.length === 0 && thead) {
                    thead.innerHTML = `<th>#</th><th>Acciones</th>`;
                }

                ejecutarFormulas(registros, FORMULAS, index);
                render_informacion();
            @endif

        }

        // Limpiar formulario

        function limpiarFormulario() {
            /*
                            let contenedor = document.getElementById('formulario-dinamico');
                            let elementos = contenedor.querySelectorAll('input, select, textarea');

                            elementos.forEach(el => {

                                //  NO limpiar hidden autocompletado
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

            const btnAgregar = document.getElementById('btn-agregar-registro');
            const texto = btnAgregar.querySelector('.btn-text');

            texto.textContent = btnAgregar.dataset.originalText;
        }
    </script>
@endif
