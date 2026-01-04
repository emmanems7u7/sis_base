@extends('layouts.argon')

@section('content')

    <div class="row">
        <div class="col-12 col-md-6 order-2 order-md-1">
            <div class="card shadow-lg mt-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h5>{{ $modulo->nombre }}</h5>
                    </div>
                    <h6 class="mt-2 d-flex align-items-center gap-2">
                        Formularios asociados al Módulo

                        <i class="fas fa-question-circle btn-tooltip"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Aquí se muestran los formularios asociados al módulo. Si alguno no aparece en la lista, es porque está inactivo. Puedes activarlo desde la configuración de módulos."
                        data-container="body"
                        data-animation="true"
                        style="cursor: pointer;">
                        </i>
                    </h6>
                    <div class="mb-2">
                        @forelse ($formularios_asociados->formularios as $formulario)
                            <small class="d-block">- {{ $formulario->nombre }}</small>
                        @empty
                            <small class="d-block">Sin Formularios Asociados</small>
                        @endforelse
                    </div>

                

                    <a href="{{ route('formularios.index') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                  
               
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



            @if($isMobile)
                @include('modulosDinamicos.iteracion_movil')
            @else
                @include('modulosDinamicos.iteracion_desktop')
            @endif

      

@endsection