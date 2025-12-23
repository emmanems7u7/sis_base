@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Nueva plantilla</h4>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('plantillas.store') }}">
                @csrf
                @include('plantillas._form')


                <a href="{{ route('plantillas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

@endsection