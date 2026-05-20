@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])
        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                <form action="{{ route('respuestas.update', ['respuesta' => $respuesta, 'modulo' => $modulo]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @foreach($formularios as $index => $formItem)

                        <div class="mb-4 border rounded p-3">

                            @if($formularios->count() > 1)
                                <h5 class="mb-3">
                                    {{ $formItem->nombre }}
                                </h5>
                            @endif


                            @php
                                $valores = [];
                                foreach ($formulario->campos as $campo) {
                                    $valores[$campo->nombre] = $respuesta->camposRespuestas
                                        ->where('cf_id', $campo->id)
                                        ->pluck('valor')
                                        ->toArray();
                                }
                            @endphp

                            @include('formularios._campos', [
                                'campos' => $formItem->campos->sortBy('posicion'),
                                'valores' => $valoresGlobal[$formItem->id] ?? [],
                                'prefix' => "form_{$formItem->id}",
                                'formulario' => $formItem,
                                'caso' => 'edit'
                            ])

                            @php
                                $formPrincipal = $formularios->first();
                            @endphp

                            @if($formPrincipal->id == $formItem->id)
                                @if(isset($formPrincipal->config['registro_multiple']) && $formPrincipal->config['registro_multiple'])

                                    <button type="button" class="btn btn-success btn-xs w-100 mt-3" id="btn-agregar-registro">
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

                                    <input type="hidden" name="registros_json" id="registros_json"value="{{ old('registros_json', $formItem->registros_json ?? '[]') }}">

                                    <div id="hidden_files_container"></div>

                                @endif
                            @endif

                        </div>

                    @endforeach


        <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">

            @if(!$moduloModelo)
                <a href="{{ route('formularios.index') }}" class="btn btn-secondary px-4 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            @else
                <a href="{{ route('modulo.index', $moduloModelo->id) }}" class="btn btn-secondary px-4 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            @endif

            <button type="submit" class="btn btn-primary px-4 py-2">
                Actualizar
            </button>

        </div>

    </form>
                </div>
            </div>

        </div>


        @include('formularios.scripts.LogicaRegistro')

@endsection