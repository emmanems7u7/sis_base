@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Crear regla de negocio</h1>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('form-logic.store') }}" method="POST">
                @include('form_logic._form')
            </form>
        </div>
    </div>

@endsection