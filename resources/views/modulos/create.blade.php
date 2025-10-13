@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Nuevo Módulo</h5>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('modulos.store') }}" method="POST">
                @csrf

                @include('modulos._form')

                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                <a href="{{ route('modulos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>

        </div>
    </div>

@endsection