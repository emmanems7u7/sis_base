@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">

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

    <div class="card mt-3 shadow-lg">
        <div class="card-body">
            <form method="POST" action="{{ route('consultas.update', $consulta->id) }}" id="formConsulta">

                @csrf
                @method('PUT')

                @include('consultas._form')

                <div class="mt-3 text-end">

                    <button type="submit" class="btn btn-primary">

                        Actualizar Consulta

                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection
