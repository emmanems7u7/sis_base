@extends('layouts.argon')

@section('content')

    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/dracula.min.css">

    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/css/css.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            CodeMirror.fromTextArea(document.getElementById('log-editor'), {
                mode: "htmlmixed",       // Puedes cambiar a javascript, xml, etc según tu log
                theme: "dracula",
                lineNumbers: true,
                readOnly: true,          // Solo lectura
                viewportMargin: Infinity // Ajusta altura automáticamente
            });
        });
    </script>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Logs del sistema</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Aquí puedes ver todos los <strong>logs del sistema</strong> ordenados por fecha.
                            Haz clic en cualquier archivo para <i class="fas fa-eye ms-1"></i> revisarlo.
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Los logs más antiguos se eliminan automáticamente según la configuración de retención.
                        </p>
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
                            <a href="{{ route('logs.show', $log) }}" class="text-primary fw-bold text-decoration-none">

                                <div class="d-flex align-items-center mb-2 p-2 rounded hover-shadow" style="cursor:pointer;">
                                    <i class="fas fa-file-alt text-primary me-3 fa-lg"></i>
                                    <div>
                                        {{ $log }}

                                        <div class="text-muted" style="font-size:0.85rem;">{{ $fecha }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        @if(isset($filename))
                            <h5>Log: {{ $filename }}</h5>
                        @endif
                        @if(isset($contenido))
                            <!-- Contenedor para CodeMirror -->
                            <textarea id="log-editor">{{ $contenido }}</textarea>

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

        .CodeMirror {
            height: 500px !important;
            /* Ocupa todo el alto del contenedor */
        }
    </style>



@endsection