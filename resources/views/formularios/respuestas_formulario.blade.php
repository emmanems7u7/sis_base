@extends('layouts.argon')


@section('content')


    @include('formularios.modal_busqueda', ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => 0])

    <div class="row">
        <div class="col-md-6 mt-2 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Respuestas del Formulario: {{ $formulario->nombre }}</h5>
                    <a href="{{ route('formularios.index') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    <a href="{{ route('formularios.registrar', ['form' => $formulario, 'modulo' => 0]) }}"
                        class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Registrar
                    </a>

                    <div class="btn-group" role="group" aria-label="Export options">

                        <div class="btn-group" role="group">
                            <button id="btnGroupExport" type="button" class="btn btn-sm btn-info dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupExport">
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="{{ route('formularios.exportPdf', $formulario) }}">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="{{ route('formularios.exportExcel', $formulario) }}">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </li>
                            </ul>
                        </div>


                    </div>

                    <a href="{{ route('formularios.carga_masiva', $formulario) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-plus"></i> Carga Masiva
                    </a>

                    <!-- Botón para abrir el modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modal_busqueda_{{ $formulario->id }}">
                        <i class="fas fa-search"></i> Buscar
                    </button>

                    <button type="button" id="activar-seleccion-masiva" class="btn btn-outline-secondary btn-sm">
                        Selección masiva
                    </button>


                </div>
            </div>
        </div>
        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Información sobre el formulario</h5>
                    <p class="text-muted">{{ $formulario->descripcion }}</p>
                </div>
            </div>
        </div>
    </div>


    {{-- modal para ver registros --}}
    @include('formularios.partials.modal_ver')


    {{-- Tabla para pantallas grandes --}}

    @if($isMobile)
        @include('formularios.respuestas_movil')
    @else
        @include('formularios.respuestas_desktop')
    @endif

    {{-- Cards para móviles --}}


    <div class="d-flex justify-content-center mt-2">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>


    @can($formulario->id . '.eliminar')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const activarSeleccion = document.getElementById('activar-seleccion-masiva');
                const checkTodos = document.getElementById('check-todos');
                const filaCheckboxes = document.querySelectorAll('.fila-checkbox');
                const checkCols = document.querySelectorAll('.check-col');
                const btnEliminar = document.getElementById('btn-eliminar-masivo');
                const inputIds = document.getElementById('respuestas_ids'); // input oculto


                btnEliminar.classList.add('d-none')
                // Función para actualizar input oculto con IDs seleccionados
                const actualizarInputIds = () => {
                    const seleccionados = Array.from(filaCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    inputIds.value = seleccionados.join(',');
                };


                // Toggle visual para el botón
                let activo = false;
                activarSeleccion.addEventListener('click', function () {
                    activo = !activo;
                    if (activo) {
                        activarSeleccion.classList.remove('btn-outline-primary');
                        activarSeleccion.classList.add('btn-primary');
                        btnEliminar.classList.remove('d-none');
                        checkCols.forEach(td => td.classList.remove('d-none'));
                        activarSeleccion.setAttribute('aria-pressed', 'true');
                    } else {
                        activarSeleccion.classList.remove('btn-primary');
                        activarSeleccion.classList.add('btn-outline-primary');
                        btnEliminar.classList.add('d-none');
                        checkCols.forEach(td => td.classList.add('d-none'));
                        checkTodos.checked = false;
                        filaCheckboxes.forEach(cb => cb.checked = false);
                        btnEliminar.disabled = true;
                        actualizarInputIds();
                        activarSeleccion.setAttribute('aria-pressed', 'false');
                    }
                });



                // 2️⃣ Checkbox maestro para marcar todas las filas
                checkTodos.addEventListener('change', function () {
                    filaCheckboxes.forEach(cb => cb.checked = this.checked);
                    btnEliminar.disabled = !this.checked;
                    actualizarInputIds();
                });

                // 3️⃣ Habilitar botón si hay al menos un checkbox seleccionado
                filaCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        const algunoSeleccionado = Array.from(filaCheckboxes).some(chk => chk.checked);
                        btnEliminar.disabled = !algunoSeleccionado;

                        if (!this.checked) checkTodos.checked = false;

                        actualizarInputIds();
                    });
                });
            });
        </script>
    @endcan

@endsection