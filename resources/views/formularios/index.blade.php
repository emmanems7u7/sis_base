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
            <div class="table-responsive">
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

                                    <a href="{{ route('formularios.campos.index', $formulario) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-list"></i> Campos
                                    </a>

                                    <a href="{{ route('formularios.edit', $formulario) }}"
                                        class="btn btn-sm btn-warning">Editar</a>




                                    <a type="button" class="btn btn-sm btn-danger " id=""
                                        onclick="confirmarEliminacion('eliminarFormulario', '¿Estás seguro de que deseas eliminar este formulario?')">Eliminar</a>

                                    <form id="eliminarFormulario" method="POST"
                                        action="{{ route('formularios.destroy', $formulario) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $formularios->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

@endsection