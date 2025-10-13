@extends('layouts.argon')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Logs del sistema</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        @foreach($logs as $log)
                            @php
                                $ruta = storage_path('logs/' . $log);
                                $fecha = date('d M Y H:i', filemtime($ruta));
                            @endphp

                            <div class="d-flex align-items-center mb-2 p-2 rounded hover-shadow" style="cursor:pointer;">
                                <i class="fas fa-file-alt text-primary me-3 fa-lg"></i>
                                <div>
                                    <a href="{{ route('logs.show', $log) }}" class="text-primary fw-bold text-decoration-none">
                                        {{ $log }}
                                    </a>
                                    <div class="text-muted" style="font-size:0.85rem;">{{ $fecha }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        @if(isset($contenido))
                            <h5>Log: {{ $filename }}</h5>
                            <pre style="white-space: pre-wrap; word-wrap: break-word;">{{ $contenido }}</pre>
                            <a href="{{ route('logs.index') }}" class="btn btn-secondary mt-3">Volver</a>
                        @else
                            <h5>Selecciona un log para ver su contenido</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
        }
    </style>
@endsection