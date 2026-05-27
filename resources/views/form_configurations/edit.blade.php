@extends('layouts.argon')

@section('content')

    <form action="{{ route('formularios.config.update', $formulario->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6 order-2 order-md-1">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5>Configuración de formulario</h5>

                        <a href="{{ route('formularios.index') }}" class="btn btn-sm btn-secondary">Volver</a>

                        <button type="submit" class="btn btn-sm btn-primary">

                            Guardar Configuración

                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 order-1 order-md-2">
                <div class="card shadow-lg">
                    <div class="card-body">

                    </div>

                </div>
            </div>
        </div>






        @include('form_configurations._form')

    </form>


@endsection