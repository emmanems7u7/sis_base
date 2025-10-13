@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Listado de Módulos</h5>
                <a href="{{ route('modulos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Módulo
                </a>

            </div>

            <div class="row">
                <div class="col-12 d-flex justify-content-end">
                    <form method="GET" action="{{ route('modulos.index') }}" class="mb-3 w-100 w-md-auto">
                        <div class="input-group" style="min-width: 300px;">
                            <input type="text" name="search" class="form-control" placeholder="Buscar módulo..."
                                value="{{ $search ?? '' }}">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>



    <div class="card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nª</th>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Padre</th>
                            <th>Formularios Asociados</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modulos as $modulo)
                            <tr>
                                <td>{{ $loop->iteration  }}</td>
                                <td>{{ $modulo->nombre }}</td>
                                <td>{{ $modulo->slug }}</td>
                                <td>{{ $modulo->padre ? $modulo->padre->nombre : '—' }}</td>
                                <td>
                                    @foreach ($modulo->formularios as $form)
                                        <span class="badge bg-secondary">{{ $form->nombre ?? 'ID ' . $form->id }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('modulos.edit', $modulo->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>


                                    <a type="button" class="btn btn-sm btn-danger" id="modal_edit_usuario_button"
                                        onclick="confirmarEliminacion('eliminarModulo{{ $modulo->id }}', '¿Estás seguro de que deseas eliminar este usuario?')">
                                        <i class="fas fa-trash"></i></a>

                                    <form id="eliminarModulo{{ $modulo->id }}" method="POST"
                                        action="{{ route('modulos.destroy', $modulo->id) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay módulos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-2">
                    {{ $modulos->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

@endsection