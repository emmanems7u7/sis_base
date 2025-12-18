@extends('layouts.argon')

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Crear regla de negocio</h5>


            </div>
            <a href="{{ route('modulo.administrar', $modulo->id) }}" class="btn btn-sm btn-secondary "><i
                    class="fas fa-arrow-left me-1"></i>Volver</a>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <form action="{{ route('form-logic.store', $modulo->id) }}" method="POST">
                @csrf
                @include('form_logic._form')


                <input type="hidden" name="acciones_json" id="acciones-json" value="">

                <button type="button" class="btn btn-primary mb-3" id="open-modal-accion">+ Agregar Acción</button>


                <button type="submit" class="btn btn-success">Crear Regla</button>

            </form>
        </div>
    </div>

    @include('form_logic.logica', [$isEdit = false])

@endsection