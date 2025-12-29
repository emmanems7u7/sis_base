@extends('layouts.argon')

@section('content')

    <div class="card mt-3">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped" id="tablaAcciones">
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
                            <tr data-detalle='@json($accion->detalle)' data-errores='@json($accion->errores)'
                                data-mensaje="{{ $accion->mensaje }}" data-tipo="{{ $accion->tipo_accion }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $accion->tipo_accion }}</td>
                                <td>{{ $accion->mensaje }}</td>
                                <td>{{ $accion->NombreUsuario }}</td>
                                <td>{{ $accion->created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-ver-detalle">
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $acciones->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal global reutilizable -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div
                class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">Detalle de acción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Mensaje:</strong> <span id="modalMensaje"></span></p>
                    <div id="modalDetalleContenido">
                        <h6>Detalle:</h6>
                        <pre id="modalDetalleJson"></pre>

                        <h6>Errores:</h6>
                        <pre id="modalErroresJson"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));

            document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');

                    const mensaje = row.dataset.mensaje;
                    const tipo = row.dataset.tipo;
                    const detalle = JSON.parse(row.dataset.detalle || '{}');
                    const errores = JSON.parse(row.dataset.errores || '{}');

                    document.getElementById('modalDetalleLabel').textContent = `Detalle de acción ${tipo}`;
                    document.getElementById('modalMensaje').textContent = mensaje;
                    document.getElementById('modalDetalleJson').textContent = JSON.stringify(detalle, null, 2);
                    document.getElementById('modalErroresJson').textContent = JSON.stringify(errores, null, 2);

                    modal.show();
                });
            });
        });
    </script>
@endsection