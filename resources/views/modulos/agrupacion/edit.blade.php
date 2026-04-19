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
            <form action="{{ route('grupos.update', ['grupo' => $grupoId, 'modulo' => $modulo->id]) }}" method="POST">
                @csrf
                @method('PUT')
                @include('modulos.agrupacion._form')
            </form>

        </div>
    </div>

@endsection