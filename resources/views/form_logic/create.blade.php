@extends('layouts.argon')

@section('content')

    <form action="{{ route('form-logic.store', $modulo->id) }}" method="POST">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Crear regla de negocio</h5>


                </div>
                <a href="{{ route('modulo.administrar', $modulo->id) }}" class="btn btn-sm btn-secondary "><i
                        class="fas fa-arrow-left me-1"></i>Volver</a>
                <button type="submit" class="btn btn-success">Crear Regla</button>

            </div>
        </div>


        @csrf
        @include('form_logic._form')


        <input type="hidden" name="acciones_json" id="acciones-json" value="">


    </form>


    @include('form_logic.logica', [$isEdit = false])

@endsection