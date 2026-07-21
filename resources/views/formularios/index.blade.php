@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Formularios</h5>

                <a href="{{ route('formularios.create') }}" class="btn btn-sm btn-primary mb-3">Nuevo Formulario</a>

            </div>
        </div>
    </div>




    @if ($isMobile)
        <div class="d-block mt-2 mb-3">
            <div class="row g-2">

                @foreach ($formularios as $formulario)
                    @php
                        $config = $formulario->config ?? [];

                        $registroMultiple = $config['registro_multiple'] ?? false;
                        $crearPermisos = $config['crear_permisos'] ?? false;
                    @endphp

                    <div class="col-12 col-sm-6 col-lg-4">

                        <div class="card border-0 shadow-sm rounded-4 h-100 animacion_card py-1"
                            data-open-offcanvas="offcanvasAccionesFormularios" data-formulario-id="{{ $formulario->id }}">

                            <div class="card-body p-3">

                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-2">

                                    <div class="flex-grow-1 pe-2">

                                        <div class="fw-bold small text-truncate">
                                            <i class="fas fa-file-alt text-primary me-1"></i>
                                            {{ $formulario->nombre }}
                                        </div>



                                    </div>

                                    <span class="badge bg-info rounded-pill px-2 py-1">
                                        {{ $formulario->estado_nombre }}
                                    </span>

                                </div>
                                <!-- Configuración -->
                                <div class="d-flex flex-wrap gap-1 mt-2 small">

                                    <!-- Registro múltiple -->
                                    <span
                                        class="badge rounded-pill px-2 py-1  {{ $registroMultiple ? 'bg-success text-white' : 'bg-warning text-white' }}">

                                        <i class="fas fa-layer-group me-1"></i>

                                        {{ $registroMultiple ? 'Multiple' : 'Simple' }}

                                    </span>

                                    <!-- Crear permisos -->
                                    <span
                                        class="badge rounded-pill px-2 py-1 {{ $crearPermisos ? 'bg-success text-white' : 'bg-warning text-white' }}">

                                        <i class="fas fa-lock me-1"></i>

                                        {{ $crearPermisos ? 'Permisos' : 'Sin permisos' }}

                                    </span>

                                </div>

                                <div class="d-none acciones-source">

                                    <div class="row w-100 g-1 row-tight justify-content-center">

                                        <div class="col-4">
                                            <a href="{{ route('formularios.respuestas.formulario', $formulario->id) }}"
                                                class="btn w-100 btn-mobile-small  btn-xs btn-outline-success">
                                                <i class="fas fa-database"></i><br> Registros
                                            </a>

                                        </div>

                                        <div class="col-4">
                                            <a href="{{ route('formularios.edit', $formulario->id) }}"
                                                class="btn w-100 btn-mobile-small  btn-xs btn-outline-warning">
                                                <i class="fas fa-pencil-alt"></i> <br> Editar
                                            </a>

                                        </div>
                                        <div class="col-4">
                                            <a type="button" class="btn w-100 btn-mobile-small  btn-xs btn-outline-danger"
                                                onclick="confirmarEliminacion('eliminarFormulario_{{ $formulario->id }}', '¿Estás seguro de que deseas eliminar este formulario?')">
                                                <i class="fas fa-trash-alt"></i> <br> Eliminar
                                            </a>

                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        <x-offcanvas-acciones id="offcanvasAccionesFormularios" titulo="Acciones Formularios" icono="fas fa-bolt"
            contenidoId="accionesContenidoFormularios" templateId="acciones-template-formularios" height="165px">

            <template id="acciones-template-formularios"></template>
        </x-offcanvas-acciones>
    @else
        <div class="card mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Columnas</th>
                                <th>Config</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($formularios as $formulario)
                                @php
                                    $config = $formulario->config ?? [];

                                    $registroMultiple = $config['registro_multiple'] ?? false;
                                    $crearPermisos = $config['crear_permisos'] ?? false;
                                @endphp

                                <tr>

                                    <td>{{ $formulario->nombre }}</td>
                                    <td>{{ $formulario->estado_nombre }}</td>

                                    <td>{{ $formulario->ColumnasCatalogo?->catalogo_descripcion }}</td>



                                    <!-- Config -->
                                    <td>

                                        <div class="d-flex flex-wrap gap-1 small">

                                            <!-- Registro múltiple -->
                                            <span
                                                class="badge rounded-pill px-2 py-1
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{ $registroMultiple ? 'bg-success text-white' : 'bg-danger text-white' }}">

                                                <i class="fas fa-layer-group me-1"></i>

                                                {{ $registroMultiple ? 'Multi' : 'Simple' }}

                                            </span>

                                            <!-- Permisos -->
                                            <span
                                                class="badge rounded-pill px-2 py-1
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{ $crearPermisos ? 'bg-success text-white' : 'bg-danger text-white' }}">

                                                <i class="fas fa-lock me-1"></i>

                                                {{ $crearPermisos ? 'Permisos' : 'Sin permisos' }}

                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <a href="{{ route('formularios.respuestas.formulario', $formulario) }}"
                                            class="btn btn-xs btn-success">

                                            <i class="fas fa-database"></i>
                                            Registros

                                        </a>

                                        <a href="{{ route('consultas.index', $formulario) }}" class="btn btn-xs btn-dark">
                                            <i class="fas fa-chart-bar"></i>
                                            Constructor de Reportes
                                        </a>

                                        <a href="{{ route('formularios.edit', $formulario) }}"
                                            class="btn btn-xs btn-warning">

                                            <i class="fas fa-pencil-alt"></i>
                                            Editar

                                        </a>

                                        <a type="button" class="btn btn-xs btn-danger"
                                            onclick="confirmarEliminacion(
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    'eliminarFormulario_{{ $formulario->id }}',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    '¿Estás seguro de que deseas eliminar este formulario?'
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                )">

                                            <i class="fas fa-trash-alt"></i>
                                            Eliminar

                                        </a>

                                        <form id="eliminarFormulario_{{ $formulario->id }}" method="POST"
                                            action="{{ route('formularios.destroy', $formulario) }}"
                                            style="display: none;">

                                            @csrf
                                            @method('DELETE')

                                        </form>

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Paginación -->
    <div class="d-flex justify-content-center">
        {{ $formularios->links('pagination::bootstrap-4') }}
    </div>



@endsection
