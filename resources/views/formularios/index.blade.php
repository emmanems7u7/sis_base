@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Formularios</h5>

                <a href="{{ route('formularios.create') }}" class="btn btn-primary mb-3">Nuevo Formulario</a>

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">

            <!-- Tabla para escritorio -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($formularios as $formulario)
                            <tr>
                                <td>{{ $formulario->nombre }}</td>
                                <td>{{ $formulario->estado_nombre }}</td>
                                <td>

                                    <a href="{{ route('formularios.respuestas.formulario', $formulario) }}"
                                        class="btn btn-sm btn-info">
                                        <i class="fas fa-database"></i> Ver Datos Registrados
                                    </a>
                                    <a href="{{ route('formularios.campos.index', $formulario) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-list-alt"></i> Campos
                                    </a>
                                    <a href="{{ route('formularios.edit', $formulario) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-pencil-alt"></i> Editar
                                    </a>
                                    <a type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmarEliminacion('eliminarFormulario_{{ $formulario->id }}','¿Estás seguro de que deseas eliminar este formulario?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>

                                    <form id="eliminarFormulario_{{ $formulario->id }}" method="POST"
                                        action="{{ route('formularios.destroy', $formulario) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Cards para móviles -->
            <div class="d-block d-md-none">
                @foreach($formularios as $formulario)
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">{{ $formulario->nombre }}</h5>
                            <p class="card-text"><strong>Estado:</strong> {{ $formulario->estado_nombre }}</p>
                            <div class="d-flex flex-wrap gap-2">

                                <a href="{{ route('formularios.respuestas.formulario', $formulario) }}"
                                    class="btn btn-sm btn-info">
                                    <i class="fas fa-database"></i> Ver Datos Registrados
                                </a>
                                <a href="{{ route('formularios.campos.index', $formulario) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-list-alt"></i> Campos
                                </a>
                                <a href="{{ route('formularios.edit', $formulario) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-pencil-alt"></i> Editar
                                </a>
                                <a type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmarEliminacion('eliminarFormulario_{{ $formulario->id }}', '¿Estás seguro de que deseas eliminar este formulario?')">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $formularios->links('pagination::bootstrap-4') }}
            </div>

        </div>
    </div>


@endsection