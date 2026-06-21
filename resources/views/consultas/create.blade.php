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

            <form method="POST" action="{{ route('consultas.store') }}" id="formConsulta">

                @csrf


                @if (isset($consulta))
                    @method('PUT')
                @endif

                @include('consultas._form')

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($consulta) ? 'Actualizar Consulta' : 'Guardar Consulta' }}
                    </button>
                </div>



            </form>

        </div>
    </div>
@endsection
