<!-- Modal para elegir ancho de columna -->
<div class="modal fade" id="colModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Elegir ancho de columna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <select id="colSize" class="form-select">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">col-{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="modal-footer">
                <button id="addColConfirm" class="btn btn-primary">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div id="filas">
    @if(isset($contenedor))
        @foreach($contenedor->filas as $fila)
            <div class="fila bg-light shadow-sm rounded p-2 mb-3 position-relative pt-5" data-id="{{ $fila->id }}">
                <button class="btn btn-xs btn-danger position-absolute top-0 end-0 m-2 removeFila" style="z-index: 10;"
                    title="Eliminar fila">X</button>

                <div class="row columnas mb-2 g-2">
                    @foreach($fila->columnas as $col)
                        <div class="{{ $col->clases_bootstrap}} columna border rounded p-3 position-relative text-center bg-white"
                            data-id="{{ $col->id }}">
                            <span class="badge bg-primary position-absolute top-0 start-0 m-1">col-{{ $col->ancho }}</span>
                            <div class="widget-dropzone">
                                @if($col->widget_id)
                                    <div class="widget-pill">
                                        <span class="widget-name">{{ $col->widget->nombre }}</span>
                                        <button class="removeWidget">&times;</button>
                                    </div>
                                @else
                                    <span style="font-size: 28px; font-weight: bold; line-height: 2;">?</span>
                                @endif
                            </div>

                            <button class="btn btn-xs btn-danger position-absolute top-0 end-0 m-1 removeCol"
                                title="Eliminar columna">X</button>
                        </div>
                    @endforeach
                    <div class="col-auto d-flex align-items-center">
                        <button class="addCol btn btn-sm btn-info">+ Col</button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    <button id="addFila" class="btn btn-success mb-2">+ Agregar fila</button>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const contenedorId = {{ $contenedor->id ?? 'null' }};

        // ------------------------------
        // Crear fila
        // ------------------------------
        document.getElementById('addFila').addEventListener('click', function () {
            fetch('/fila/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    contenedor_id: contenedorId,
                    posicion: document.querySelectorAll('.fila').length
                })
            })
                .then(res => res.json())
                .then(fila => {
                    const filasContainer = document.getElementById('filas');

                    // Crear la fila
                    const nuevaFila = document.createElement('div');
                    nuevaFila.className = "fila bg-light shadow-sm rounded p-2 mb-3 position-relative pt-5";
                    nuevaFila.dataset.id = fila.id;
                    nuevaFila.innerHTML = `
            <button class="btn btn-xs btn-danger position-absolute top-0 end-0 m-2 removeFila" style="z-index:10;" title="Eliminar fila">X</button>
            <div class="row columnas mb-2 g-2">
                <div class="col-auto d-flex align-items-center">
                    <button class="addCol btn btn-sm btn-info">+ Col</button>
                </div>
            </div>
        `;

                    // Insertar la fila **antes del botón +Agregar fila**
                    const addFilaBtn = document.getElementById('addFila');
                    filasContainer.insertBefore(nuevaFila, addFilaBtn);

                    initAll(); // Inicializa eventos y sortable
                });
        });

        // ------------------------------
        // Inicialización general
        // ------------------------------
        function initAddColButtons() {
            document.querySelectorAll('.addCol').forEach(btn => {
                btn.onclick = function () {
                    const filaDiv = this.closest('.fila');
                    window.currentFilaId = filaDiv.dataset.id;
                    new bootstrap.Modal(document.getElementById('colModal')).show();
                };
            });
        }

        document.getElementById('addColConfirm').addEventListener('click', function () {
            const ancho = document.getElementById('colSize').value;
            const fila_id = window.currentFilaId;
            fetch('/columna/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    fila_id: fila_id,
                    ancho: ancho,
                    posicion: document.querySelector(`[data-id='${fila_id}'] .columna`)?.length || 0
                })
            })
                .then(res => res.json())
                .then(col => {
                    const filaDiv = document.querySelector(`.fila[data-id='${fila_id}'] .row.columnas`);

                    // Crear nueva columna
                    const nuevaCol = document.createElement('div');
                    nuevaCol.className = `col-${col.ancho} columna border rounded p-3 position-relative text-center bg-white`;
                    nuevaCol.dataset.id = col.id;
                    nuevaCol.innerHTML = `
            <span class="badge bg-primary position-absolute top-0 start-0 m-1">col-${col.ancho}</span>
            <span style="font-size:28px; font-weight:bold; line-height:2;">?</span>
            <button class="btn btn-xs btn-danger position-absolute top-0 end-0 m-1 removeCol" title="Eliminar columna">X</button>
        `;

                    // Insertar antes del botón +Col siempre
                    const addColDiv = filaDiv.querySelector('.col-auto');
                    filaDiv.insertBefore(nuevaCol, addColDiv);

                    bootstrap.Modal.getInstance(document.getElementById('colModal')).hide();
                    initAll();
                });
        });

        function initSortableFilas() {
            new Sortable(document.getElementById('filas'), {
                animation: 150,
                handle: '.fila',
                onEnd: e => {
                    const orden = [...document.querySelectorAll('.fila')].map(f => f.dataset.id);
                    fetch('/fila/ordenar', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{csrf_token()}}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ orden })
                    });
                }
            });
        }

        function initSortableColumns() {
            document.querySelectorAll('.columnas').forEach(row => {
                new Sortable(row, {
                    animation: 150,
                    onEnd: e => {
                        const orden = [...row.children].map(c => c.dataset.id);
                        fetch('/columna/ordenar', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{csrf_token()}}', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ orden })
                        });
                    }
                });
            });
        }

        function initRemoveButtons() {
            document.querySelectorAll('.removeFila').forEach(btn => {
                btn.onclick = function () {
                    const filaDiv = this.closest('.fila');
                    const filaId = filaDiv.dataset.id;
                    alertify.confirm('Eliminar fila', '¿Estás seguro de eliminar esta fila?',
                        function () {
                            fetch(`/fila/${filaId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{csrf_token()}}', 'Content-Type': 'application/json' } })
                                .then(res => res.json())
                                .then(resp => { if (resp.success) filaDiv.remove(); });
                        },
                        function () { alertify.error('Acción cancelada'); }
                    );
                };
            });

            document.querySelectorAll('.removeCol').forEach(btn => {
                btn.onclick = function () {
                    const colDiv = this.closest('.columna');
                    const colId = colDiv.dataset.id;
                    alertify.confirm('Eliminar columna', '¿Estás seguro de eliminar esta columna?',
                        function () {
                            fetch(`/columna/${colId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{csrf_token()}}', 'Content-Type': 'application/json' } })
                                .then(res => res.json())
                                .then(resp => { if (resp.success) colDiv.remove(); });
                        },
                        function () { alertify.error('Acción cancelada'); }
                    );
                };
            });
        }

        function initWidgetsDrag() {
            document.querySelectorAll('.widgets-panel').forEach(panel => {
                new Sortable(panel, {
                    group: {
                        name: 'widgets',
                        pull: 'clone',
                        put: false
                    },
                    sort: false,
                    animation: 150
                });
            });
        }
        function initDropZones() {
            document.querySelectorAll('.widget-dropzone').forEach(zone => {

                new Sortable(zone, {
                    group: 'widgets',
                    animation: 150,
                    onAdd: function (evt) {

                        const widgetId = evt.item.dataset.id;
                        const columnaId = zone.closest('.columna').dataset.id;
                        console.log({ widgetId, columnaId });
                        fetch('/columna/asignar-widget', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                columna_id: columnaId,
                                widget_id: widgetId
                            })
                        });

                        zone.innerHTML = `
    <div class="widget-pill">
        <span class="widget-name">${evt.item.innerText}</span>
        <button class="removeWidget">&times;</button>
    </div>
`;
                    }
                });

            });
        }
        function initRemoveWidgetBtn() {
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('removeWidget')) {

                    const colDiv = e.target.closest('.columna');
                    const colId = colDiv.dataset.id;

                    fetch('/columna/quitar-widget', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ columna_id: colId })
                    });

                    colDiv.querySelector('.widget-dropzone').innerHTML =
                        `<span style="font-size: 28px; font-weight: bold; line-height: 2;">?</span>`;
                }
            });
        }

        function initAll() {
            initAddColButtons();
            initSortableColumns();
            initRemoveButtons();
            initSortableFilas();
            initWidgetsDrag();
            initDropZones();
            initRemoveWidgetBtn();
        }
        initAll();
    });


</script>