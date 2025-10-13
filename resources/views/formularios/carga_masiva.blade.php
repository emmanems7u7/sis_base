@extends('layouts.argon')

@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Carga Masiva de información</h5>

                    <a href="{{ route('formularios.descargar.plantilla', $form) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Descargar plantilla
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">


                    <i class="fas fa-file-upload me-2 fa-sm text-info"></i>
                    <div class="small">
                        <strong>Carga masiva:</strong> selecciona un archivo <code>.txt</code> o <code>.csv</code> con los
                        datos separados por comas. <br>
                        <i class="fas fa-info-circle text-secondary"></i> Los campos de tipo <em>imagen, video o
                            archivo</em> deben contener solo una <strong>ruta genérica</strong>. <br>
                        <i class="fas fa-exclamation-triangle text-warning"></i> Los <strong>errores detectados</strong> se
                        mostrarán en el contenedor inferior después de la importación.
                    </div>



                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <form action="{{ route('formularios.importar', $form) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="archivo" class="form-label fw-bold">
                                <i class="fas fa-file-upload me-2 text-primary"></i> Carga masiva de datos
                            </label>

                            <input type="file" name="archivo" id="archivo" class="form-control form-control-sm"
                                accept=".txt,.csv" required>


                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-upload me-1"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <div class="col-md-6">
            <div class="card shadow-lg mt-3">
                <div class="card-body">
                    <h5 class="mb-3 text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Errores encontrados:
                    </h5>

                    @if (session('erroresImportacion'))
                        <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                            <ul class="mb-0 text-danger small">
                                @foreach (session('erroresImportacion') as $error)
                                    <li class="mb-1">{!! $error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-1"></i>No se detectaron errores durante la importación.
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection