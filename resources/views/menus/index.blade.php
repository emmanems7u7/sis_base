@extends('layouts.argon')

@section('content')

    @include('menus.create_seccion')
    @include('menus.create_menu')
    
    <div class="row">

<div class="col-md-6 mt-2 order-1 order-md-2">
    <div class="card shadow-lg">
        <div class="card-body">
            <h5>
                <i class="fas fa-sitemap me-2"></i>
                Gestión de Secciones y Menús
            </h5>

            <small>
                <i class="fas fa-cogs me-1"></i>
                En este módulo se gestiona la estructura general de los menús y secciones dinámicas del sistema.
            </small><br>

            <small>
                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                Al eliminar una sección, <strong>todos los menús asociados</strong> a ella serán eliminados automáticamente.
                Realice esta acción con precaución.
            </small><br>

            <small>
                <i class="fas fa-user-shield me-1"></i>
                <strong> Atención:</strong> si al crear una sección o menú <strong>no aparece en el menú lateral</strong>, es porque primero
                debe ser asignado a un rol específico desde
                <a href="{{ route('roles.index') }}" class="text-primary fw-bold">
                    Gestión de Roles
                </a>.
            </small>
        </div>
    </div>
</div>

<div class="col-md-6 order-2 order-md-1 mt-2">
    <div class="card shadow-lg">
        <div class="card-body">
            <h5>Acciones Disponibles</h5>

            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearSeccionModal">
                Crear Sección
            </button>

            <button type="button" class="btn btn-primary mb-3" id="btn-crea-menu"
                data-bs-toggle="modal" data-bs-target="#crearMenuModal">
                Crear Menú
            </button>
        </div>
    </div>
</div>

</div>
    <div class="row">
        <div class="col-md-4 mt-3">
             <!-- Sección de Secciones -->
            <div class="card shadow-lg">
                
                <div class="card-body">

                    @if($secciones->isEmpty())
                        <p>No hay secciones disponibles.</p>
                    @else
                        <p>Lista de Secciones disponibles en el sistema.</p>
                        <ul class="list-group list-group-flush">
                        @foreach($secciones as $seccion)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="small">
                                <i class="fas fa-folder-open me-2 text-primary"></i>
                                {{ $seccion->titulo }}
                            </span>
                                <form action="{{ route('secciones.destroy', $seccion->id) }}" method="POST"
                                    id="delete-form-{{ $seccion->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-xs btn-outline-danger"
                                        onclick="confirmarEliminacion('delete-form-{{ $seccion->id }}','¿Eliminar sección?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
                    @endif
            </div>
        </div>
        <div class="col-md-8 mt-3">

        <div class="card shadow-lg">
        
        <div class="card-body">
          

            @if($menus->isEmpty())
                <p>No hay menús disponibles.</p>
            @else
            <p>Lista de Menús disponibles en el sistema.</p>
            <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                   
                    <th>Menú</th>
                    <th>Sección</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menus as $menu)
                    <tr>
                    <td class="small">
                        <i class="fas fa-bars me-2 text-primary"></i>
                        {{ $menu->nombre }}
                    </td>

                    <td>
                        <span class="badge bg-secondary small">
                            {{ $menu->seccion->titulo }}
                        </span>
                    </td>
                    <td class="text-center">
                            
                            <form action="{{ route('menus.destroy', $menu->id) }}" method="POST"
                                  id="delete-form-{{ $menu->id }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-xs btn-outline-danger"
                                    onclick="confirmarEliminacion('delete-form-{{ $menu->id }}','¿Eliminar menú?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $menus->links('pagination::bootstrap-4') }}
    </div>
            @endif
        </div>
    </div>
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