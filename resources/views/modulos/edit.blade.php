@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('modulos.update', $modulo->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('modulos._form')


                <a href="{{ route('modulos.index') }}" class="btn btn-secondary">Cancelar</a> <button type="submit"
                    class="btn btn-success"><i class="fas fa-save"></i> Actualizar</button>
            </form>

        </div>
    </div>

@endsection