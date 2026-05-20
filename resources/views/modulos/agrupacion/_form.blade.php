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
                <div class="row g-3 align-items-stretch">

                    <!-- RELACION MULTIPLE -->
                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100 bg-light">

                            <div class="d-flex align-items-center mb-2">

                                <i class="fas fa-project-diagram text-warning me-2"></i>

                                <span class="fw-bold">
                                    Relación
                                </span>

                            </div>

                            <div class="form-check form-switch">

                                <input class="form-check-input" type="checkbox" id="relacion_multiple"
                                    name="relacion_multiple" value="1">

                                <label class="form-check-label ms-1" for="relacion_multiple">

                                    Válido para relación 1:N

                                </label>

                            </div>

                            <small class="text-muted d-block mt-2">

                                Permite definir si la operación se aplicará en casos de relación 1:N entre los
                                formularios seleccionados.

                            </small>

                        </div>

                    </div>

                    <!-- TIPO CALCULO -->
                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100 bg-light">

                            <div class="d-flex align-items-center mb-2">

                                <i class="fas fa-calculator text-primary me-2"></i>

                                <span class="fw-bold">
                                    Tipo de cálculo
                                </span>

                            </div>

                            <select class="form-select" id="tipo_calculo">

                                <option value="fila">
                                    Por fila
                                </option>

                                <option value="global">
                                    Global
                                </option>

                                <option value="grupo">
                                    Por grupo
                                </option>

                            </select>

                            <small class="text-muted d-block mt-2">

                                Define cómo se ejecutará la fórmula.

                            </small>

                        </div>

                    </div>

                </div>
                <!-- FILA PRINCIPAL -->
                <div class="d-flex flex-wrap align-items-center gap-2">

                    <div style="width: 180px;">
                        <small>Destino</small>
                        <div id="drop_destino" class="border p-2 text-center bg-light" style="min-height: 42px;">
                            Arrastra aquí
                        </div>
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
                    <button type="button" class="btn btn-primary btn-xs operador" data-op="="
                        draggable="true">=</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="+">+</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="-">-</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="*">*</button>
                    <button type="button" class="btn btn-primary btn-xs operador" draggable="true"
                        data-op="/">/</button>
                </div>

                <!-- FUNCIONES -->
                <div class="mt-2 d-flex gap-1 flex-wrap">

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="SUM">
                        SUM
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="AVG">
                        AVG
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="IF">
                        IF
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="ROUND">
                        ROUND
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="COUNT">
                        COUNT
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="MIN">
                        MIN
                    </button>

                    <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="MAX">
                        MAX
                    </button>

                </div>

                <div class="mt-2 d-flex gap-1">

                    <button type="button" class="btn btn-secondary btn-xs agrupador" draggable="true" data-group="(">
                        (
                    </button>

                    <button type="button" class="btn btn-secondary btn-xs agrupador" draggable="true" data-group=")">
                        )
                    </button>

                </div>
                <hr>
                <!-- TABLA -->
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Destino</th>
                            <th>Fórmula</th>
                            <th>Modo</th>
                            <th>Tipo cálculo</th>

                            <th>Relación</th>
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


    // AUTO CONTINUAR EN EDIT
    @if(isset($seleccionados) && count($seleccionados) === 2 && isset($principal))
        setTimeout(() => {
            document.getElementById('btn_continuar').click();
        }, 300);
    @endif

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

                    @if(isset($configOperaciones) && count($configOperaciones) > 0)
                        cargarOperacionesExistentes(@json($configOperaciones));
                    @endif
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
                    color: 'primary',
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
                valor: this.dataset.op,
                color: 'dark'
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

                this.appendChild(
                    crearBadge(data, 'success')
                );

            } else {

                this.appendChild(
                    crearBadge(data, data.color || 'primary')
                );

            }

            if (typeof activarBotonesYDrag === 'function') activarBotonesYDrag();
        });
    });

    function crearBadge(data, color) {

        let span = document.createElement('span');

        span.className = `badge bg-${color} d-inline-flex align-items-center me-1 mb-1`;

        span.style.cursor = 'pointer';

        let texto = document.createElement('span');

        // TEXTO DEL BADGE
        if (data.tipo === 'campo') {

            texto.innerText = data.nombre;

        }
        else if (data.tipo === 'operador') {

            texto.innerText = data.valor;

        }
        else if (data.tipo === 'funcion') {

            texto.innerText = data.nombre;

        }
        else if (data.tipo === 'agrupador') {

            texto.innerText = data.valor;

        }

        span.appendChild(texto);

        // BOTON X
        let x = document.createElement('span');

        x.innerHTML = '&times;';

        x.style.color = 'red';

        x.style.marginLeft = '5px';

        x.style.fontWeight = 'bold';

        span.appendChild(x);

        // DATASETS
        span.dataset.tipo = data.tipo;

        if (data.tipo === 'campo') {

            span.dataset.nombre = data.nombre;
            span.dataset.campo = data.campo;
            span.dataset.campo_id = data.campo_id;
            span.dataset.form = data.form;
            span.dataset.principal = data.principal;

        }
        else if (data.tipo === 'operador') {

            span.dataset.valor = data.valor;

        }
        else if (data.tipo === 'funcion') {

            span.dataset.nombre = data.nombre;

        }
        else if (data.tipo === 'agrupador') {

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
            else if (el.dataset.tipo === 'funcion') {

                formula.push({
                    tipo: 'funcion',
                    nombre: el.dataset.nombre
                });

            }
            else if (el.dataset.tipo === 'agrupador') {

                formula.push({
                    tipo: 'agrupador',
                    valor: el.dataset.valor
                });

            }
        });

        if (formula.length === 0) {
            mostrarAlerta('warning', 'Construye una operación');
            return;
        }

        // DETECTAR MODO
        let modo = 'calculo';

        // SOLO: = campo
        if (
            formula.length === 2 &&
            formula[0].tipo === 'operador' &&
            formula[0].valor === '=' &&
            formula[1].tipo === 'campo'
        ) {

            modo = 'asignacion';

        }

        // SI EXISTE FUNCION
        if (
            formula.some(f => f.tipo === 'funcion')
        ) {

            modo = 'funcion';

        }

        let relacionMultiple = document.getElementById('relacion_multiple')?.checked ? 1 : 0;
        let tipo_calculo = document.getElementById('tipo_calculo')?.value || null;

        let operacion = {
            id: Date.now(),
            destino: destino,
            formula: formula,
            relacion_multiple: relacionMultiple,
            tipo_calculo: tipo_calculo,
            modo: modo
        };

        operaciones.push(operacion);
        let formulaTexto = formula.map(f => {

            if (f.tipo === 'campo') {
                return f.nombre;
            }

            if (f.tipo === 'operador') {
                return f.valor;
            }

            if (f.tipo === 'funcion') {
                return f.nombre;
            }

            if (f.tipo === 'agrupador') {
                return f.valor;
            }

            return '';

        }).join(' ');

        let badgeModo = '';
        let badgeCalculo = '';


        // BADGE MODO
        if (operacion.modo === 'asignacion') {

            badgeModo = `
    <span class="badge bg-info">
        Asignación
    </span>
`;

        }
        else if (operacion.modo === 'calculo') {

            badgeModo = `
    <span class="badge bg-primary">
        Cálculo
    </span>
`;

        }
        else if (operacion.modo === 'funcion') {

            badgeModo = `
    <span class="badge bg-warning text-dark">
        Función
    </span>
`;

        }


        // BADGE TIPO CALCULO
        if (operacion.tipo_calculo === 'fila') {

            badgeCalculo = `
    <span class="badge bg-secondary">
        Por fila
    </span>
`;

        }
        else if (operacion.tipo_calculo === 'global') {

            badgeCalculo = `
    <span class="badge bg-dark">
        Global
    </span>
`;

        }
        else if (operacion.tipo_calculo === 'grupo') {

            badgeCalculo = `
    <span class="badge bg-success">
        Por grupo
    </span>
`;

        }


        let fila = `
<tr data-id="${operacion.id}">

<td>
    ${destino.nombre}
</td>

<td>
    ${formulaTexto}
</td>

<td>
    ${badgeModo}
</td>

<td>
    ${badgeCalculo}
</td>

<td>
    ${operacion.relacion_multiple
                ? '<span class="badge bg-warning text-dark">1:N</span>'
                : '<span class="badge bg-light text-dark border">1:1</span>'
            }
</td>

<td>
    <button
        type='button'
        class="btn btn-danger btn-xs btn-eliminar">

        <i class="fas fa-trash"></i>

    </button>
</td>

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
    function cargarOperacionesExistentes(data) {

        operaciones = data;

        let tabla = document.getElementById('tabla_operaciones');

        tabla.innerHTML = '';

        data.forEach(op => {

            let destino = op.destino.nombre;

            let formulaTexto = op.formula.map(f => {

                if (f.tipo === 'campo') {
                    return f.nombre;
                }

                if (f.tipo === 'operador') {
                    return f.valor;
                }

                if (f.tipo === 'funcion') {
                    return f.nombre;
                }

                if (f.tipo === 'agrupador') {
                    return f.valor;
                }

                return '';

            }).join(' ');


            // BADGE MODO
            let badgeModo = '';

            if (op.modo === 'asignacion') {

                badgeModo = `
            <span class="badge bg-info">
                Asignación
            </span>
        `;

            }
            else if (op.modo === 'calculo') {

                badgeModo = `
            <span class="badge bg-primary">
                Cálculo
            </span>
        `;

            }
            else if (op.modo === 'funcion') {

                badgeModo = `
            <span class="badge bg-warning text-dark">
                Función
            </span>
        `;

            }


            // BADGE TIPO CALCULO
            let badgeCalculo = '';

            if (op.tipo_calculo === 'fila') {

                badgeCalculo = `
            <span class="badge bg-secondary">
                Por fila
            </span>
        `;

            }
            else if (op.tipo_calculo === 'global') {

                badgeCalculo = `
            <span class="badge bg-dark">
                Global
            </span>
        `;

            }
            else if (op.tipo_calculo === 'grupo') {

                badgeCalculo = `
            <span class="badge bg-success">
                Por grupo
            </span>
        `;

            }


            let fila = `
    <tr data-id="${op.id}">

        <td>
            ${destino}
        </td>

        <td>
            ${formulaTexto}
        </td>

        <td>
            ${badgeModo}
        </td>

        <td>
            ${badgeCalculo}
        </td>

        <td>
            ${op.relacion_multiple
                    ? '<span class="badge bg-warning text-dark">1:N</span>'
                    : '<span class="badge bg-light text-dark border">1:1</span>'
                }
        </td>

        <td>

            <button
                type='button'
                class="btn btn-danger btn-xs btn-eliminar">

                <i class="fas fa-trash"></i>

            </button>

        </td>

    </tr>
    `;

            tabla.insertAdjacentHTML('beforeend', fila);

        });

        // ACTIVAR ELIMINAR
        document.querySelectorAll('.btn-eliminar').forEach(btn => {

            btn.onclick = function () {

                let tr = this.closest('tr');

                let id = tr.dataset.id;

                operaciones = operaciones.filter(op => op.id != id);

                tr.remove();

                actualizarHidden();

            };

        });

        actualizarHidden();

    }
    document.querySelectorAll('.funcion').forEach(btn => {

        btn.addEventListener('dragstart', function (e) {

            e.dataTransfer.effectAllowed = 'move';

            e.dataTransfer.setData('application/json', JSON.stringify({
                tipo: 'funcion',
                nombre: this.dataset.func,
                color: 'warning'
            }));

        });

    });

    document.querySelectorAll('.agrupador').forEach(btn => {

        btn.addEventListener('dragstart', function (e) {

            e.dataTransfer.effectAllowed = 'move';

            e.dataTransfer.setData('application/json', JSON.stringify({
                tipo: 'agrupador',
                valor: this.dataset.group,
                color: 'secondary'
            }));

        });

    });
</script>