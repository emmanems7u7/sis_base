@extends('layouts.argon')

@section('content')

    @include('menus.create_seccion')
    @include('menus.create_menu')
    <!-- Sección de Secciones -->
    <div class="card mt-3">
        <div class="card-header">

            <h2>Secciones disponibles</h2>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearSeccionModal">
                Crear Sección
            </button>

        </div>
        <div class="card-body">

            @if($secciones->isEmpty())
                <p>No hay secciones disponibles.</p>
            @else
                <p>Lista de Secciones disponibles en el sistema.</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nª</th>
                            <th>Título</th>

                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($secciones as $seccion)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $seccion->titulo }}</td>

                                <td>
                                    <a href="{{ route('secciones.edit', $seccion->id) }}" class="btn btn-warning">Editar</a>
                                    <form action="{{ route('secciones.destroy', $seccion->id) }}" method="POST"
                                        id="delete-form-{{ $seccion->id }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger"
                                            onclick="confirmarEliminacion('delete-form-{{ $seccion->id }}' , '¿Estás seguro de eliminar esta sección?')">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @endif
        </div>
    </div>
    <hr>
    <div class="card">
        <div class="card-header">
            <h2>Menús</h2>
        </div>
        <div class="card-body">
            <!-- Botón para crear menú -->
            <button type="button" class="btn btn-primary mb-3" id="btn-crea-menu" data-bs-toggle="modal"
                data-bs-target="#crearMenuModal">
                Crear Menú
            </button>

            @if($menus->isEmpty())
                <p>No hay menús disponibles.</p>
            @else
                <div class="row">
                    @foreach($menus as $menu)
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $menu->nombre }}</h5>
                                    <p class="card-text">Sección: {{ $menu->seccion->titulo }}</p>

                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-warning btn-sm">
                                            Editar
                                        </a>
                                        <form action="{{ route('menus.destroy', $menu->id) }}" method="POST"
                                            id="delete-form-{{ $menu->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmarEliminacion('delete-form-{{ $menu->id }}' , '¿Estás seguro de eliminar este menú?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center">
                    {{ $menus->links('pagination::bootstrap-4') }} <!-- Enlaces de paginación para los menús -->
                </div>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Verifica si hay errores de validación
            let hasErrors = @json($errors->any());

            if (hasErrors) {

                document.getElementById('btn-crea-menu').click();


            }

        });
    </script>


@endsection