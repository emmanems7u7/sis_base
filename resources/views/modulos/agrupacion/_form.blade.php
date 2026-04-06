<div class="row mb-3">
    <div class="col-md-3  mt-3 ">


        <div class="card shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Nombre del grupo</label>
                    <input type="text" name="grupo" class="form-control" value="{{ old('grupo', $grupoNombre ?? '') }}"
                        placeholder="Ej: A" required>
                </div>
                <h6 class="mb-3">
                    <i class="fas fa-layer-group"></i> Formularios disponibles
                </h6>

                @forelse ($formularios as $form)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                        <div class="form-check">
                            <input class="form-check-input check-formulario" type="checkbox" name="formularios[]"
                                value="{{ $form->id }}" id="form_{{ $form->id }}" {{ in_array($form->id, $seleccionados ?? []) ? 'checked' : '' }}>

                            <label class="form-check-label" for="form_{{ $form->id }}">
                                @if($form->config['registro_multiple'])
                                    <i class="fas fa-question-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="Permite Registros multiples"></i>
                                @endif
                                {{ $form->nombre }}



                            </label>
                        </div>
                        @if($form->config['registro_multiple'])
                            <div class="form-check">
                                <input class="form-check-input radio-principal" type="radio" name="principal"
                                    value="{{ $form->id }}" {{ ($principal ?? null) == $form->id ? 'checked' : '' }}>

                                <label class="form-check-label">
                                    Principal
                                </label>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="alert alert-warning mb-0">
                        No hay formularios disponibles
                    </div>
                @endforelse

                <input type="hidden" name="operaciones_json" id="operaciones_json" value="">
            </div>
            <div class="card-footer  d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-xs btn-info" id="btn_continuar"> Continuar</button>

            </div>
        </div>
    </div>
    <div class="col-md-9  mt-3 ">

        <div id="contenedor_campos"></div>


        <div id="contenedor_operaciones" class="card mt-3 d-none">
            <div class="card-body">

                <!-- FILA PRINCIPAL -->
                <div class="d-flex flex-wrap align-items-center gap-2">

                    <div style="width: 180px;">
                        <small>Destino</small>
                        <div id="drop_destino" class="border p-2 text-center bg-light" style="min-height: 42px;">
                            Arrastra aquí
                        </div>
                    </div>

                    <div class="fw-bold fs-4 mt-3">
                        =
                    </div>

                    <div class="flex-grow-1">
                        <small>Operación</small>
                        <div id="drop_operacion" class="border p-2 d-flex flex-wrap gap-1 bg-light"
                            style="min-height: 42px;">
                        </div>
                    </div>

                    <div class="mt-2 mt-md-0">
                        <button type="button" class="btn btn-success btn-sm" onclick="guardarOperacion()">
                            Agregar
                        </button>
                    </div>

                </div>

                <!-- OPERADORES -->
                <div class="mt-3 d-flex gap-1">
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="+">+</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="-">-</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="*">*</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="/">/</button>
                </div>



                <hr>

                <!-- TABLA -->
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Destino</th>
                            <th>Fórmula</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tabla_operaciones"></tbody>
                </table>

            </div>

            <div class="card-footer d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-dark btn-sm px-4">Cancelar</button>
                <button type="submit" class="btn btn-success btn-sm px-4">Guardar Asociación</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {

        const maxSeleccion = 2;

        const checks = document.querySelectorAll('.check-formulario');
        const radios = document.querySelectorAll('.radio-principal');

        function actualizarEstado() {
            let seleccionados = document.querySelectorAll('.check-formulario:checked');

            checks.forEach(check => {
                if (!check.checked) {
                    check.disabled = seleccionados.length >= maxSeleccion;
                }
            });

            radios.forEach(radio => {
                let check = document.querySelector(`.check-formulario[value="${radio.value}"]`);

                if (check && check.checked) {
                    radio.disabled = false;
                } else {
                    radio.disabled = true;

                    if (radio.checked) {
                        radio.checked = false;
                    }
                }
            });
        }

        checks.forEach(check => {
            check.addEventListener('change', function () {

                let seleccionados = document.querySelectorAll('.check-formulario:checked');

                if (seleccionados.length > maxSeleccion) {
                    this.checked = false;
                    return;
                }

                actualizarEstado();
            });
        });

        radios.forEach(radio => {
            radio.addEventListener('click', function (e) {

                let check = document.querySelector(`.check-formulario[value="${this.value}"]`);

                if (!check || !check.checked) {
                    e.preventDefault();
                }
            });
        });

        actualizarEstado();

    });




</script>


<script>
    let operaciones = [];
    document.getElementById('btn_continuar').addEventListener('click', function () {

        let seleccionados = Array.from(document.querySelectorAll('.check-formulario:checked'))
            .map(el => el.value);

        let principal = document.querySelector('.radio-principal:checked')?.value;

        if (seleccionados.length === 0) {
            mostrarAlerta('warning', 'Selecciona al menos un formulario');
            return;
        }

        if (seleccionados.length < 2) {
            mostrarAlerta('warning', 'Debes seleccionar 2 formularios');
            return;
        }

        if (seleccionados.length > 2) {
            mostrarAlerta('warning', 'Solo puedes seleccionar 2 formularios');
            return;
        }
        if (!principal) {
            mostrarAlerta('warning', 'Selecciona un formulario principal');
            return;
        }

        fetch("{{ route('formularios.campos.multiples') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                formularios: seleccionados,
                principal: principal
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderCampos(data.data);
                    var contenedorOperaciones = document.getElementById('contenedor_operaciones');
                    contenedorOperaciones.classList.remove('d-none');
                }
            })
            .catch(err => console.error(err));

    });

    function renderCampos(formularios) {

        let contenedor = document.getElementById('contenedor_campos');
        contenedor.innerHTML = '';

        let row = document.createElement('div');
        row.className = 'row';

        formularios.forEach(form => {

            let col = document.createElement('div');
            col.className = 'col-md-6';

            let html = `
                        <div class="card mb-3">
                            
                            <div class="card-body">
                                
                                <div class="fw-bold mb-2 text-primary">
                                    ${form.nombre}
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                        `;

            form.campos.forEach(campo => {

                html += `
                            <button 
                                type="button" 
                                class="btn btn-primary btn-xs campo-draggable"
                                draggable="true"
                                data-campo_id="${campo.id}"
                                data-nombre="${campo.nombre}"
                                data-campo="${campo.campo_nombre}"
                                data-form="${form.id}"
                                data-principal="${form.principal ? 1 : 0}"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="${campo.campo_nombre}">
                                
                                ${campo.etiqueta}
                            </button>
                        `;
            });

            html += `
                            </div>

                        </div>
                    </div>
                    `;

            col.innerHTML = html;
            row.appendChild(col);
        });

        contenedor.appendChild(row);

        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        document.querySelectorAll('.campo-draggable').forEach(btn => {

            btn.addEventListener('dragstart', function (e) {

                let data = {
                    tipo: 'campo',
                    nombre: this.dataset.nombre,
                    campo: this.dataset.campo,
                    campo_id: this.dataset.campo_id,
                    form: this.dataset.form,
                    principal: this.dataset.principal
                };

                e.dataTransfer.setData('application/json', JSON.stringify(data));
            });

        });

    }

    document.querySelectorAll('.operador').forEach(btn => {
        btn.addEventListener('dragstart', function (e) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('application/json', JSON.stringify({
                tipo: 'operador',
                valor: this.dataset.op
            }));
        });
    });

    ['drop_destino', 'drop_operacion'].forEach(id => {
        let zona = document.getElementById(id);
        if (!zona) return;

        zona.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        zona.addEventListener('drop', function (e) {
            e.preventDefault();

            let raw = e.dataTransfer.getData('application/json');
            if (!raw) return;

            let data = JSON.parse(raw);

            if (this.id === 'drop_destino') {
                if (data.tipo !== 'campo') return;
                this.innerHTML = '';
                this.appendChild(crearBadge(data, 'success'));
            } else {
                let color = data.tipo === 'operador' ? 'dark' : 'primary';
                this.appendChild(crearBadge(data, color));
            }

            if (typeof activarBotonesYDrag === 'function') activarBotonesYDrag();
        });
    });

    function crearBadge(data, color) {
        let span = document.createElement('span');
        span.className = `badge bg-${color} d-inline-flex align-items-center me-1 mb-1`;
        span.style.cursor = 'pointer';

        let texto = document.createElement('span');
        texto.innerText = data.tipo === 'operador' ? data.valor : data.nombre;
        span.appendChild(texto);

        let x = document.createElement('span');
        x.innerHTML = '&times;'; // ×
        x.style.color = 'red';
        x.style.marginLeft = '5px';
        x.style.fontWeight = 'bold';
        span.appendChild(x);

        span.dataset.tipo = data.tipo;
        if (data.tipo === 'campo') {
            span.dataset.nombre = data.nombre;
            span.dataset.campo = data.campo;
            span.dataset.campo_id = data.campo_id;
            span.dataset.form = data.form;
            span.dataset.principal = data.principal;
        } else if (data.tipo === 'operador') {
            span.dataset.valor = data.valor;
        }

        x.onclick = function (e) {
            e.stopPropagation();
            span.remove();
        };

        span.draggable = true;

        return span;
    }
    function crearBadgeOperador(data) {
        let cont = document.createElement('div');
        cont.className = 'badge-container d-inline-flex align-items-center me-1 mb-1';
        cont.draggable = true;
        cont.dataset.tipo = 'operador';
        cont.dataset.valor = data.valor;

        let span = document.createElement('span');
        span.className = 'badge bg-dark';
        span.innerText = data.valor;
        cont.appendChild(span);

        let btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-danger ms-1 btn-remove';
        btn.innerHTML = '<i class="fas fa-times"></i>';
        cont.appendChild(btn);

        return cont;
    }

    function activarBotonesYDrag() {
        document.querySelectorAll('.btn-remove').forEach(btn => {
            btn.onclick = function () {
                this.closest('.badge-container').remove();
            };
        });
    }
    function guardarOperacion() {
        let destinoEl = document.querySelector('#drop_destino span');
        if (!destinoEl) {
            mostrarAlerta('warning', 'Selecciona campo destino');
            return;
        }

        let destino = {
            tipo: destinoEl.dataset.tipo,
            nombre: destinoEl.dataset.nombre,
            campo_id: destinoEl.dataset.campo_id,
            form: destinoEl.dataset.form,
        };

        let formula = [];
        document.querySelectorAll('#drop_operacion span.badge').forEach(el => {
            if (!el.dataset.tipo) return;

            if (el.dataset.tipo === 'operador') {
                formula.push({
                    tipo: 'operador',
                    valor: el.dataset.valor
                });
            } else if (el.dataset.tipo === 'campo') {
                formula.push({
                    tipo: 'campo',
                    nombre: el.dataset.nombre,
                    campo_id: el.dataset.campo_id,
                    form: el.dataset.form
                });
            }
        });

        if (formula.length === 0) {
            mostrarAlerta('warning', 'Construye una operación');
            return;
        }

        let operacion = {
            id: Date.now(),
            destino: destino,
            formula: formula
        };

        operaciones.push(operacion);

        // Renderizar la fila en la tabla
        let fila = `
        <tr data-id="${operacion.id}">
            <td>${destino.nombre}</td>
            <td>${formula.map(f => f.tipo === 'campo' ? f.nombre : f.valor).join(' ')}</td>
            <td><button type='button' class="btn btn-danger btn-xs btn-eliminar">X</button></td>
        </tr>
    `;
        document.getElementById('tabla_operaciones').insertAdjacentHTML('beforeend', fila);

        // Actualizar el input hidden
        actualizarHidden();

        // Configurar botón eliminar
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.onclick = function () {
                let tr = this.closest('tr');
                let id = tr.dataset.id;
                operaciones = operaciones.filter(op => op.id != id);
                tr.remove();
                actualizarHidden(); // actualizar hidden después de eliminar
            };
        });

        // Limpiar droppable
        document.getElementById('drop_destino').innerHTML = 'Arrastra aquí';
        document.getElementById('drop_operacion').innerHTML = '';
    }

    // Función para actualizar input hidden
    function actualizarHidden() {
        let inputHidden = document.getElementById('operaciones_json');
        if (!inputHidden) {

            inputHidden = document.createElement('input');
            inputHidden.type = 'hidden';
            inputHidden.id = 'operaciones_json';
            inputHidden.name = 'operaciones_json'; // nombre para backend
            document.querySelector('#contenedor_operaciones').appendChild(inputHidden);
        }
        inputHidden.value = JSON.stringify(operaciones);
    }
</script>