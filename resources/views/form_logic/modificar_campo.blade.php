<div id="modal-modificar-campo" class="d-none">
    <div class="row g-3">

        <div class="col-md-4">
            <label>Formulario Destino</label>
            <select id="modal-form-ref" class="form-select">
                <option value="">-- Seleccionar Formulario --</option>
                @foreach ($formularios as $form)
                    <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Formulario origen</label>
            <select class="form-select select-formulario" id="formulario_id" name="formulario_id">
                <option value="">Seleccione un formulario...</option>

                @foreach ($formularios as $form)
                    <option value="{{ $form->id }}"
                        {{ old('formulario_id', $rule->formulario_id ?? '') == $form->id ? 'selected' : '' }}>
                        {{ $form->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>
    <div class="row mt-3">



        <div class="col-md-6">

            <div class="card h-100">

                <div class="card-body">
                    <h6>Campos formulario destino</h6>
                    <div id="campos-destino" class="d-flex flex-wrap gap-1">

                    </div>

                </div>

            </div>

        </div>
        <div class="col-md-6">

            <div class="card h-100">



                <div class="card-body">

                    <h6>Campos formulario origen</h6>
                    <div id="campos-origen" class="d-flex flex-wrap gap-1">

                    </div>

                </div>

            </div>

        </div>
    </div>

    <div class="row mt-4">

        <div class="col-md-3">

            <small>Destino</small>

            <div id="drop_destino" class="border p-2 bg-light" style="min-height:45px">
            </div>

        </div>

        <div class="col-md-9">

            <small>Expresión</small>

            <div id="drop_formula" class="border p-2 bg-light d-flex flex-wrap gap-1" style="min-height:45px">
            </div>

        </div>

    </div>
    <div class="row">
        <!-- OPERADORES -->
        <div class="mt-3 d-flex flex-wrap gap-1">
            <button type="button" class="btn btn-primary btn-xs operador" data-op="=" draggable="true">=</button>
            <button type="button" class="btn btn-primary btn-xs operador" draggable="true" data-op="+">+</button>
            <button type="button" class="btn btn-primary btn-xs operador" draggable="true" data-op="-">-</button>
            <button type="button" class="btn btn-primary btn-xs operador" draggable="true" data-op="*">*</button>
            <button type="button" class="btn btn-primary btn-xs operador" draggable="true" data-op="/">/</button>
        </div>

        <!-- FUNCIONES -->
        <div class="mt-2 d-flex gap-1 flex-wrap">

            <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="SUM">
                SUM
            </button>

            <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="DELTA">
                DELTA
            </button>
            <button type="button" class="btn btn-warning btn-xs funcion" draggable="true" data-func="INV">
                INV
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

            <button type="button" class="btn btn-secondary btn-xs agrupador" draggable="true" data-group=",">
                ,
            </button>
        </div>

    </div>

    <div class="row mt-4">

        <div class="col-12">

            <button type="button" class="btn btn-success btn-sm" id="btn-agregar-asignacion">

                Agregar asignación

            </button>

        </div>

    </div>

    <div class="row mt-3">

        <div class="col-12">

            <div id="lista-asignaciones" class="row g-2">

            </div>

        </div>

    </div>
</div>


<script>
    let asignaciones = [];
    // LOGICA PARA CARGAR CAMPOS Y HACERLOS ARRASTRABLES
    function renderCamposDraggables(formId, contenedorId, tipo = 'origen') {

        const contenedor = document.getElementById(contenedorId);

        if (!formId) {
            contenedor.innerHTML = '';
            return;
        }

        cargarCamposConCache(formId, document.createElement('select'), tipo)
            .then(() => {

                const campos = camposCache[tipo][formId] || [];

                contenedor.innerHTML = '';

                campos.forEach(campo => {

                    const btn = document.createElement('button');

                    btn.type = 'button';
                    btn.className = 'btn btn-primary btn-xs campo-draggable';

                    btn.draggable = true;

                    btn.dataset.tipo = 'campo';
                    btn.dataset.nombre = campo.nombre;
                    btn.dataset.campo = campo.campo_nombre ?? '';
                    btn.dataset.campo_id = campo.id;
                    btn.dataset.form = formId;
                    btn.dataset.contexto = tipo;

                    btn.innerText = campo.nombre;

                    contenedor.appendChild(btn);

                });

                activarDragCampos();

            });

    }

    function activarDragCampos() {

        document.querySelectorAll('.campo-draggable').forEach(btn => {

            btn.addEventListener('dragstart', function(e) {

                let data = {

                    tipo: 'campo',
                    nombre: this.dataset.nombre,
                    campo: this.dataset.campo,
                    campo_id: this.dataset.campo_id,
                    form: this.dataset.form,
                    contexto: this.dataset.contexto

                };

                e.dataTransfer.setData(
                    'application/json',
                    JSON.stringify(data)
                );

            });

        });

    }

    function crearToken(data) {

        const token = document.createElement('span');

        token.className = 'badge rounded-pill me-1 mb-1 p-2 d-inline-flex align-items-center';

        switch (data.tipo) {

            case 'campo':
                token.classList.add(
                    data.contexto === 'origen' ?
                    'bg-primary' :
                    'bg-success'
                );
                break;

            case 'operador':
                token.classList.add('bg-dark');
                break;

            case 'funcion':
                token.classList.add('bg-warning', 'text-dark');
                break;

            case 'agrupador':
                token.classList.add('bg-secondary');
                break;
        }

        token.dataset.token = JSON.stringify(data);

        // Texto
        const texto = document.createElement('span');

        switch (data.tipo) {
            case 'campo':
                texto.textContent = data.nombre;
                break;

            case 'operador':
                texto.textContent = data.valor;
                break;

            case 'funcion':
                texto.textContent = data.nombre;
                break;

            case 'agrupador':
                texto.textContent = data.valor;
                break;
        }

        // Botón eliminar
        const cerrar = document.createElement('span');
        cerrar.innerHTML = '&times;';
        cerrar.className = 'ms-2';
        cerrar.style.cursor = 'pointer';
        cerrar.style.fontWeight = 'bold';
        cerrar.style.fontSize = '14px';
        cerrar.style.color = 'red';

        cerrar.addEventListener('click', function(e) {
            e.stopPropagation();
            token.remove();
        });

        token.appendChild(texto);
        token.appendChild(cerrar);

        return token;

    }

    function obtenerTokens(contenedorId) {

        return [...document.querySelector(`#${contenedorId}`).children]
            .map(e => JSON.parse(e.dataset.token));

    }

    document.getElementById('formulario_id').addEventListener('change', function() {

        renderCamposDraggables(
            this.value,
            'campos-origen',
            'origen'
        );

    });
    document.getElementById('modal-form-ref').addEventListener('change', function() {

        renderCamposDraggables(
            this.value,
            'campos-destino',
            'destino'
        );

    });

    function activarDragElementos() {

        document.querySelectorAll('.operador').forEach(btn => {

            btn.addEventListener('dragstart', function(e) {

                e.dataTransfer.setData(
                    'application/json',
                    JSON.stringify({
                        tipo: 'operador',
                        valor: this.dataset.op
                    })
                );

            });

        });

        document.querySelectorAll('.funcion').forEach(btn => {

            btn.addEventListener('dragstart', function(e) {

                e.dataTransfer.setData(
                    'application/json',
                    JSON.stringify({
                        tipo: 'funcion',
                        nombre: this.dataset.func
                    })
                );

            });

        });

        document.querySelectorAll('.agrupador').forEach(btn => {

            btn.addEventListener('dragstart', function(e) {

                e.dataTransfer.setData(
                    'application/json',
                    JSON.stringify({
                        tipo: 'agrupador',
                        valor: this.dataset.group
                    })
                );

            });

        });

    }
</script>


<script>
    function inicializarDrops() {

        const dropDestino = document.getElementById('drop_destino');
        const dropFormula = document.getElementById('drop_formula');

        [dropDestino, dropFormula].forEach(drop => {

            drop.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

        });

        dropDestino.addEventListener('drop', function(e) {

            e.preventDefault();

            const data = JSON.parse(e.dataTransfer.getData('application/json'));

            if (data.tipo !== 'campo') {
                mostrarAlerta('warning', 'El destino solo admite campos.');
                return;
            }

            if (data.contexto !== 'destino') {
                mostrarAlerta('warning', 'Solo puede seleccionar un campo del formulario destino.');
                return;
            }

            this.innerHTML = '';

            this.appendChild(crearToken(data));

        });

        dropFormula.addEventListener('drop', function(e) {

            e.preventDefault();

            const data = JSON.parse(e.dataTransfer.getData('application/json'));

            this.appendChild(crearToken(data));

        });

    }

    document.getElementById('btn-agregar-asignacion').addEventListener('click', agregarAsignacion);

    function limpiarConstructor() {

        document.getElementById('drop_destino').innerHTML = '';

        document.getElementById('drop_formula').innerHTML = '';

    }

    function renderAsignaciones() {

        const lista = document.getElementById('lista-asignaciones');

        lista.innerHTML = '';

        asignaciones.forEach(asignacion => {

            const formula = asignacion.formula.map(t => {

                switch (t.tipo) {
                    case 'campo':
                        return t.nombre;
                    case 'operador':
                        return t.valor;
                    case 'funcion':
                        return t.nombre;
                    case 'agrupador':
                        return t.valor;
                }

            }).join(' ');

            const fila = document.createElement('div');

            fila.className = 'd-flex align-items-center justify-content-between border rounded px-2 py-1 mb-2';

            fila.innerHTML = `
        <div class="text-truncate flex-grow-1 me-2">
            <span class="badge bg-success me-1">${asignacion.destino.nombre}</span>
            ${formula}
        </div>

        <button
            type="button"
            class="btn btn-outline-danger btn-sm"
            onclick="eliminarAsignacion(${asignacion.id})">

            <i class="fas fa-trash"></i>

        </button>
    `;

            lista.appendChild(fila);

        });
        console.log(asignaciones);
    }

    function eliminarAsignacion(id) {

        asignaciones = asignaciones.filter(a => a.id != id);

        renderAsignaciones();

    }

    function agregarAsignacion() {

        const destino = obtenerTokens('drop_destino');
        const formula = obtenerTokens('drop_formula');

        if (destino.length === 0) {
            mostrarAlerta('warning', 'Seleccione un campo destino.');
            return;
        }

        if (formula.length === 0) {
            mostrarAlerta('warning', 'Construya una expresión.');
            return;
        }

        const asignacion = {

            id: Date.now(),

            modo: detectarModo(formula),

            destino: destino[0],

            formula: formula

        };

        asignaciones.push(asignacion);

        renderAsignaciones();

        limpiarConstructor();

    }

    function detectarModo(formula) {

        // Si comienza con una función
        if (formula.length > 0 && formula[0].tipo === 'funcion') {
            return 'funcion';
        }

        // Si existe algún operador matemático
        if (formula.some(t =>
                t.tipo === 'operador' && ['+', '-', '*', '/'].includes(t.valor)
            )) {
            return 'calculo';
        }

        // Caso contrario
        return 'asignacion';

    }
    inicializarDrops();
    activarDragElementos();
</script>
