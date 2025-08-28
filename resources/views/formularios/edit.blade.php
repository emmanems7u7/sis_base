@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Editar Formulario</h5>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('formularios.update', $formulario) }}" method="POST">
                @csrf
                @method('PUT')
                @include('formularios._form', ['formulario' => $formulario])
            </form>
        </div>
    </div>

@endsection