@extends('layouts.argon')

@section('content')

    <div class="row">
        <div class="col-12 col-md-6 order-2 order-md-1">
            <div class="card shadow-lg mt-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h5>{{ $modulo->nombre }}</h5>
                    </div>

                    <h6 class="mt-2"><i class="fas fa-file-alt me-2"></i>Formularios asociados al Módulo</h6>

                    <div class="mb-2">
                        @forelse ($modulo->formularios as $formulario)
                            <small class="d-block">- {{ $formulario->nombre }}</small>
                        @empty
                            <small class="d-block">Sin Formularios Asociados</small>
                        @endforelse
                    </div>

                    <div>
                        <button class="btn btn-sm btn-info"><i class="fas fa-plus me-2"></i>Agregar</button>

                        <div class="btn-group" role="group" aria-label="Export options">

                            <div class="btn-group" role="group">
                                <button id="btnGroupExport" type="button" class="btn btn-sm btn-info dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export"></i> Exportar
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupExport">
                                    <li>
                                        <a class="dropdown-item" target="_blank" href="">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" target="_blank" href="">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12">
                                <form action="" method="GET">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Buscar"
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
        </div>
        <div class="col-12 col-md-6 order-1 order-md-2">
            <div class="card shadow-lg mt-2">
                <div class="card-body">

                    {!!   $modulo->descripcion !!}
                </div>
            </div>
        </div>
    </div>


    <div class="card shadow-lg mt-3">
        <div class="card-body">
            <h3>{{ $modulo->nombre }}</h3>

            @if($isMobile)
                @include('modulosDinamicos.iteracion_movil')
            @else
                @include('modulosDinamicos.iteracion_desktop')
            @endif

        </div>
    </div>

@endsection