@extends('layouts.argon')

@section('content')
    <div class="container my-4">

        @include('formularios.contenedor_superior', ['formulario' => $formulario])


        <div class="card mt-3 shadow-lg">
            <div class="card-body">
                <form action="{{ route('formularios.responder', $formulario->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @include('formularios._campos', ['campos' => $formulario->campos->sortBy('posicion'), 'valores' => []])

                    <a href="{{ route('formularios.index') }}" class="btn btn-secondary mt-3"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    <button type="submit" class="btn btn-primary mt-3">Enviar formulario</button>
                </form>
            </div>
        </div>

    </div>
@endsection