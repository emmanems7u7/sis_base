@extends('layouts.argon')

@section('content')
    @include('permisos.create')
    @include('permisos.edit')



    <div class="row">
        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                    <small class="d-block mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        En este módulo puedes <strong>ver y filtrar todos los permisos</strong> disponibles en el sistema.
                    </small>

                    <small class="d-block mb-2">
                        <i class="fas fa-tags me-1"></i>
                        Haz clic en un módulo para <strong>filtrar permisos específicos</strong> de ese módulo.
                    </small>

                    <small class="d-block mb-2">
                        <i class="fas fa-key me-1"></i>
                        Cada permiso puede ser <strong>eliminado</strong> directamente usando el botón de la card o del
                        badge.
                    </small>

                    <small class="d-block mb-0 text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Atención: eliminar un permiso <strong>afectará todos los roles que lo tengan asignado</strong>.
                        Algunos permisos están <strong>restringidos por seguridad del sistema</strong> y no pueden
                        eliminarse.
                    </small>

                </div>
            </div>
        </div>

        <div class="col-md-6 order-2 order-md-1 mt-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Módulo de Permisos del sistema</h5>


                    <a href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearPermiso">Crear
                        Nuevo Permiso</a>



                </div>
            </div>
        </div>

    </div>



    <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6"></div>
    </div>


    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">

                    <h5>Lista de permisos dinámicos</h5>


                    <div class="card shadow-lg mt-2 mb-2">
                        <div class="card-body p-3">
                            <p class="mb-2">Permisos dinámicos por Categoria</p>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @foreach ($cat_permisosD as $modulo)
                                    <form method="GET" action="{{ route('permissions.index') }}">
                                        <input type="hidden" name="search2" value="{{ $modulo }}.">
                                        <button type="submit" class="btn btn-outline-info btn-sm px-2 py-1 small">
                                            {{ ucfirst($modulo) }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>

                        </div>
                    </div>

                    <div class="row">

                        @forelse($permissionsDinamic as $permiso)

                            <div class="col-md-6 mb-2 mt-3">
                                <div class="d-flex align-items-center justify-content-between  rounded px-3 py-2 shadow-sm">

                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-key text-primary me-2"></i>
                                        <span class="text-truncate" style="">{{$permiso->nombreParaVista() }}</span>
                                    </div>
                                    <form action="{{ route('permissions.destroy', $permiso->id) }}" method="POST"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este permiso?')" class="mb-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger p-1" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>


                                </div>
                            </div>

                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning text-center">No hay permisos que coincidan.</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-center mt-2">
                        {{ $permissionsDinamic->links('pagination::bootstrap-4') }}
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-6 mb-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Permisos Definidos en el Sistema</h5>

                    <div class="card shadow-lg mt-2 mb-2">
                        <div class="card-body p-3">
                            <p class="mb-2">Permisos por Categoria</p>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @foreach ($cat_permisos as $modulo)
                                    <form method="GET" action="{{ route('permissions.index') }}">
                                        <input type="hidden" name="search" value="{{ $modulo }}.">
                                        <button type="submit" class="btn btn-outline-info btn-sm px-2 py-1 small">
                                            {{ ucfirst($modulo) }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>

                        </div>
                    </div>

                    <div class="row">

                        @forelse($permissions as $permiso)

                            <div class="col-md-6 mb-2 mt-3">
                                <div class="d-flex align-items-center justify-content-between  rounded px-3 py-2 shadow-sm">

                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-key text-primary me-2"></i>
                                        <span class="text-truncate" style="">{{ $permiso->name }}</span>
                                    </div>



                                </div>
                            </div>

                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning text-center">No hay permisos que coincidan.</div>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-center mt-2">
                        {{ $permissions->links('pagination::bootstrap-4') }}
                    </div>


                </div>
            </div>
        </div>

    </div>








@endsection