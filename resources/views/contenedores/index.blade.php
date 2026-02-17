@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <a href="/contenedor/create" class="btn btn-primary mb-3">+ Nuevo Contenedor</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contenedores as $c)
                            <tr>
                                <td>{{ $c->nombre }}</td>
                                <td>{{ $c->role->name }}</td>
                                <td class="d-flex gap-1">

                                    <!-- Botón Editar -->
                                    <a href="{{ route('contenedor.edit', ['id' => $c->id]) }}" class="btn btn-sm btn-warning">
                                        Editar
                                    </a>

                                    <!-- Botón Eliminar -->
                                    <a type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmarEliminacion('eliminarContenedorForm_{{ $c->id }}', '¿Estás seguro de que deseas eliminar este contenedor?')">
                                        Eliminar
                                    </a>
                                    <form id="eliminarContenedorForm_{{ $c->id }}" method="POST"
                                        action="{{ route('contenedor.store') }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <!-- Botón Configuración -->
                                    <a href="{{ route('contenedor.conf', ['id' => $c->id]) }}" class="btn btn-sm btn-info">
                                        Config
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection