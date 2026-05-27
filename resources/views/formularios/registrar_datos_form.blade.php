@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])


        

                <form
                    action="{{ route('formularios.responder', ['form' => $formulario->id, 'modulo' => $modulo, 'tipo' => 0]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf


                  
                    @foreach($formularios as $index => $formItem)

                  <div class="card mt-2 shadow-lg">
                      <div class="card-body">
                          
                   
                        @if($formularios->count() > 1)
                            <h6 class="">
                                {{ $formItem->nombre }}
                            </h6>
                            <div class="w-100 mb-3" style="height:2px; background:#e9ecef; border-radius:2px;"></div>
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

                        <button type="button" class="btn btn-outline-success btm-xs w-100 mt-3" id="btn-agregar-registro">
                            {!! configForm($formItem->id, 'titles.add_multiple') !!}
                        </button>

                                <div class="mt-1">
                                    <h6>  {!! configForm($formItem->id, 'titles.registers_adds') !!}</h6>

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
                  </div>

                    @endforeach


                 
                    @include('formularios.partials.Botones_RA_footer', 
                    ['texto' => 'Registrar', 
                    'icono' => 'fas fa-plus',
                    'moduloModelo' => $moduloModelo])

                </form>
               

    </div>



@include('formularios.scripts.LogicaRegistro')
@include('formularios.scripts.LogicaRegistro2')

@endsection