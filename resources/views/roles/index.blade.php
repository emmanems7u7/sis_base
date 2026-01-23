@extends('layouts.argon')

@section('content')

    <div class="row">
        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">


                    <small class="d-block mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        En este módulo puedes <strong>ver todos los roles</strong> registrados en el sistema y sus permisos
                        asignados.
                    </small>

                    <small class="d-block mb-2">
                        <i class="fas fa-sitemap me-1"></i>
                        Cada card muestra las <strong>secciones y menús dinámicos</strong> a los que tiene acceso el rol.
                    </small>

                    <small class="d-block mb-2">
                        <i class="fas fa-key me-1"></i>
                        También se muestran los <strong>permisos específicos</strong> asignados a cada rol.
                    </small>

                    <small class="d-block mb-0 text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Atención: si modificas los permisos o secciones de un rol, esto afectará inmediatamente los accesos
                        de los usuarios asociados a ese rol.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-6 order-2 order-md-1 mt-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Módulo de Roles del sistema</h5>


                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>

                    <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary">Crear Nuevo Rol</a>




                </div>
            </div>
        </div>

    </div>

    <div class="row mt-3">
        @foreach($roles as $role)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-user-tag me-2"></i> {{ $role->name }}
                        </h5>

                        <p class="mb-2 text-muted"><strong>Permisos:</strong></p>
                        <div style="max-height: 122px; overflow-y: auto; padding-right: 10px;">
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>
                            @endforeach
                        </div>


                    </div>
                    <div class="card-footer">


                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;"
                            id="delete-form-{{ $role->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="confirmarEliminacion('delete-form-{{ $role->id }}' , '¿Estás seguro de eliminar este rol?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </form>
                        <a href="{{ route('roles.edit', ['id' => $role->id]) }}" class="btn btn-sm btn-warning me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>

                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <script>
        /*
        function confirmDelete(roleId) {
            alertify.confirm(
                'Confirmar Eliminación',
                '¿Estás seguro de eliminar este rol?',
                function () {

                    document.getElementById('delete-form-' + roleId).submit();
                },
                function () {

                    alertify.error('Eliminación cancelada');
                }
            ).set('labels', { ok: 'Eliminar', cancel: 'Cancelar' }); // Opcional: Cambia los textos de los botones
        }*/
    </script>

@endsection