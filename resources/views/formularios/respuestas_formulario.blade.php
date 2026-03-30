@extends('layouts.argon')


@section('content')


    @include('formularios.modal_busqueda', ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => 0])

    <div class="row">
        <div class="col-md-6 mt-2 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Respuestas del Formulario: {{ $formulario->nombre }}</h5>
                    <a href="{{ route('formularios.index') }}" class="btn btn-xs btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @include('modulosDinamicos.botones_accion', ['formulario' => $formulario, 'modulo' => 0])


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


    {{-- modal para ver registros --}}
    @include('formularios.partials.modal_ver')


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