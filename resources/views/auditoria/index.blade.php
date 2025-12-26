@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo de Acción</th>
                        <th>Mensaje</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($acciones as $accion)
                        <tr>
                            <td>{{ $accion->action_id }}</td>
                            <td>{{ $accion->tipo_accion }}</td>
                            <td>{{ $accion->mensaje }}</td>
                            <td>{{ $accion->usuario_id }}</td>
                            <td>{{ $accion->created_at }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalDetalle{{ $accion->action_id }}">
                                    Ver detalle
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="modalDetalle{{ $accion->action_id }}" tabindex="-1"
                                    aria-labelledby="modalLabel{{ $accion->action_id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel{{ $accion->action_id }}">Detalle de
                                                    acción {{ $accion->tipo_accion }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Mensaje:</strong> {{ $accion->mensaje }}</p>

                                                @if(!empty($accion->detalle))
                                                    <h6>Detalle:</h6>
                                                    <pre>{{ json_encode($accion->detalle, JSON_PRETTY_PRINT) }}</pre>
                                                @endif

                                                @if(!empty($accion->errores))
                                                    <h6>Errores:</h6>
                                                    <pre>{{ json_encode($accion->errores, JSON_PRETTY_PRINT) }}</pre>
                                                @endif

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fin modal -->

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection