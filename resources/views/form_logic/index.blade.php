@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Reglas de negocio</h1>
                <a href="{{ route('form-logic.create') }}" class="btn btn-primary mb-3">Crear nueva regla</a>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Formulario Origen</th>
                        <th>Evento</th>
                        <th>Acciones</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $rule)
                        <tr>
                            <td>{{ $rule->nombre }}</td>
                            <td>{{ $rule->formulario->nombre }}</td>
                            <td>{{ $rule->evento }}</td>
                            <td>
                                @foreach($rule->actions as $act)
                                    <div>
                                        {{ $act->OperacionCatalogo }} → {{ $act->formularioDestino->nombre ?? 'Sin formulario' }}
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('form-logic.edit', $rule->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('form-logic.delete', $rule->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection