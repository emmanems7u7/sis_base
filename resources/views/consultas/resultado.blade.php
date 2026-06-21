@extends('layouts.argon')

@section('content')
    <div class="container-fluid py-3">

        <!-- Encabezado -->
        <div class="card shadow-sm border-0 mb-3">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div>
                        <h4 class="mb-1">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            {{ $consulta->nombre }}
                        </h4>

                        <small class="text-muted">
                            Reporte generado:
                            {{ now()->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <div class="mt-2 mt-md-0">

                        <button class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>
                            PDF
                        </button>

                        <button class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>
                            Excel
                        </button>

                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-print me-1"></i>
                            Imprimir
                        </button>

                    </div>

                </div>

            </div>

        </div>

        <!-- Resumen -->
        <div class="row mb-3">

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="text-muted text-uppercase small">
                            Registros
                        </div>

                        <h3 class="mb-0">
                            {{ $resultado->count() }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="text-muted text-uppercase small">
                            Columnas
                        </div>

                        <h3 class="mb-0">
                            {{ count($columnas) }}
                        </h3>

                    </div>

                </div>

            </div>

        </div>

        <!-- Tabla -->
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Resultados
                    </h6>

                    <span class="badge bg-primary">
                        {{ $resultado->count() }} registros
                    </span>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-items-center mb-0">

                    <thead class="bg-light">

                        <tr>

                            <th width="60">
                                #
                            </th>

                            @foreach ($columnas as $columna)
                                <th class="text-uppercase text-xs font-weight-bolder">
                                    {{ $columna }}
                                </th>
                            @endforeach

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($resultado as $index => $fila)
                            <tr>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $index + 1 }}
                                    </span>
                                </td>

                                @foreach ($fila as $valor)
                                    <td>
                                        {{ $valor }}
                                    </td>
                                @endforeach

                            </tr>

                        @empty

                            <tr>

                                <td colspan="{{ count($columnas) + 1 }}" class="text-center py-5">

                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>

                                    <p class="mb-0 text-muted">
                                        No se encontraron registros.
                                    </p>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>
@endsection
