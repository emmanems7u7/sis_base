@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])


        

        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                <form
                    action="{{ route('formularios.responder', ['form' => $formulario->id, 'modulo' => $modulo, 'tipo' => 0]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf


                  
                    @foreach($formularios as $index => $formItem)

                    <div class="mb-4 border rounded p-3">
                        
                        @if($formularios->count() > 1)
                            <h5 class="mb-3">
                                {{ $formItem->nombre }}
                            </h5>
                        @endif

                        @include('formularios._campos', [
                            'campos' => $formItem->campos->sortBy('posicion'),
                            'valores' => [],
                            'formulario' => $formItem,
                            'prefix' => "form_{$formItem->id}" ,
                            'caso' => 'store'
                        ])

                        @php
                        $formPrincipal = $formularios->first();
                    @endphp
                        @if($formPrincipal->id == $formItem->id)
                        @if(isset($formPrincipal->config['registro_multiple']) && $formPrincipal->config['registro_multiple'])

                        <button type="button" class="btn btn-success btm-xs w-100 mt-3" id="btn-agregar-registro">
    Agregar
</button>

<div class="mt-1">
    <h6>Registros agregados</h6>

    @if($isMobile)
        <div id="contenedor-cards"></div>
    @else
        <div id="contenedor-tabla" class="table-responsive">
            <table class="table table-bordered table-striped" id="tabla-registros">
                <thead>
                    <tr id="thead-dinamico">
                        <th>#</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endif
</div>

            <input type="hidden" name="registros_json" id="registros_json"
            
                value="{{ old('registros_json') }}">
               
            <div id="hidden_files_container"></div>

@endif
@endif
                    </div>

                    @endforeach


                 


                    <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">

                        @if(!$moduloModelo)
                            <a href="{{ route('formularios.index') }}" 
                            class="btn btn-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        @else
                            <a href="{{ route('modulo.index', $moduloModelo->id) }}" 
                            class="btn btn-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        @endif

                        <button type="submit" class="btn btn-primary px-4 py-2">
                            Registrar
                        </button>

                    </div>
                </form>
            </div>
        </div>

    </div>



@include('formularios.scripts.LogicaRegistro')

@endsection