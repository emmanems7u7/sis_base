@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])
        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                <form action="{{ route('respuestas.update', ['respuesta' => $respuesta ,'modulo' => $modulo ]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $valores = [];
                        foreach ($formulario->campos as $campo) {
                            $valores[$campo->nombre] = $respuesta->camposRespuestas
                                ->where('cf_id', $campo->id)
                                ->pluck('valor')
                                ->toArray();
                        }
                    @endphp

                    @include('formularios._campos', ['campos' => $formulario->campos->sortBy('posicion'), 'valores' => $valores, 'form' => $formulario->id])


                    <button type="submit" class="btn btn-primary mt-3">Actualizar Respuesta</button>
                </form>
            </div>
        </div>

    </div>
@endsection