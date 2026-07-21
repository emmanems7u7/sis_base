@extends('layouts.argon')


@section('content')
    @include('formularios.modal_busqueda', [
        'formulario' => $formulario,
        'campos' => $formulario->campos,
        'modulo' => 0,
    ])

    <div class="row">
        <div class="col-md-6 mt-2 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">

                    <h5> {!! configForm($formulario->id, 'titles.index') !!}</h5>

                    <a href="{{ route('formularios.index') }}" class="btn btn-xs btn-secondary me-1">
                        {!! configForm($formulario->id, 'titles.back', null) !!}</a>

                    <a href="{{ route('formularios.respuestas.formulario', $formulario) }}" class="btn btn-xs btn-secondary ">
                        {!! configForm($formulario->id, 'titles.remove_filters', null) !!}</a>

                    @include('formularios.partials.botones_accion', [
                        'formulario' => $formulario,
                        'modulo' => 0,
                    ])


                </div>
            </div>
        </div>
        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>{!! configForm($formulario->id, 'titles.info', null) !!}</h5>
                    <p class="text-muted">{!! $formulario->descripcion !!}</p>
                </div>
            </div>
        </div>
    </div>


    {{-- modal para ver registros --}}
    @include('formularios.partials.modal_ver')


    {{-- Tabla para pantallas grandes --}}

    @if ($isMobile)
        @include('formularios.respuestas_movil')
    @else
        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                @include('formularios.respuestas_desktop')

            </div>
        </div>
    @endif
@endsection
