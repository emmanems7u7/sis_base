@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Plantillas de correo</h4>
                <a href="{{ route('plantillas.create') }}" class="btn btn-primary btn-sm">
                    Nueva plantilla
                </a>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Archivo</th>
                        <th>Estado</th>
                        <th width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plantillas as $p)
                        <tr>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->archivo }}</td>
                            <td>
                                <span class="badge {{ $p->estado ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $p->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('plantillas.edit', $p) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>


                                <a type="button" class="btn btn-sm btn-danger" id="modal_edit_usuario_button"
                                    onclick="confirmarEliminacion('eliminar_plantilla{{ $p->id }}', '¿Estás seguro de que deseas eliminar esta plantilla?')">
                                    <i class="fas fa-trash"></i></a>

                                <form id="eliminar_plantilla{{ $p->id }}" method="POST"
                                    action="{{ route('plantillas.destroy', $p->id) }}" style="display: none;">
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

@endsection