@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <a href="{{ route('formularios.campos.index', $principal) }}" class="btn btn-dark btn-sm px-4">Volver</a>
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

    <form action="{{ route('grupos.store', 0) }}" method="POST">
        @csrf
        @include('modulos.agrupacion._form')
    </form>
@endsection
