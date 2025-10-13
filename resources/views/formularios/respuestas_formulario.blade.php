@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6 mt-2 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Respuestas del Formulario: {{ $formulario->nombre }}</h5>
                    <a href="{{ route('formularios.index') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    <a href="{{ route('formularios.registrar', $formulario) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                    <div class="btn-group" role="group" aria-label="Export options">

                        <div class="btn-group" role="group">
                            <button id="btnGroupExport" type="button" class="btn btn-sm btn-info dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-export"></i> Exportar
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="btnGroupExport">
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="{{ route('formularios.exportPdf', $formulario) }}">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                        href="{{ route('formularios.exportExcel', $formulario) }}">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </li>
                            </ul>
                        </div>


                    </div>

                    <a href="{{ route('formularios.carga_masiva', $formulario) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-plus"></i> Carga Masiva
                    </a>

                    <div class="row mb-2">
                        <div class="col-md-12">
                            <form action="{{ route('formularios.respuestas.formulario', $formulario->id) }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre"
                                        value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Información sobre el formulario</h5>
                    <p class="text-muted">{{ $formulario->descripcion }}</p>
                </div>
            </div>
        </div>
    </div>


    {{-- Tabla para pantallas grandes --}}

    @if($isMobile)
        @include('formularios.respuestas_movil')
    @else
        @include('formularios.respuestas_desktop')
    @endif

    {{-- Cards para móviles --}}


    <div class="d-flex justify-content-center mt-2">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>


@endsection