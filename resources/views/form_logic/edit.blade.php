@extends('layouts.argon')

@section('content')
    <form action="{{ route('form-logic.update', ['rule' => $rule->id, 'modulo' => $modulo->id]) }}" method="POST">

        <div class="row">
            <div class="col-md-6 order-2 order-md-1">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Editar regla de negocio</h5>
                        </div>
                        <a href="{{ route('modulo.administrar', $modulo->id) }}" class="btn btn-sm btn-secondary "><i
                                class="fas fa-arrow-left me-1"></i>Volver</a>
                        <button type="submit" class="btn btn-sm btn-success"> Editar Regla</button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 order-1 order-md-2">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <small>
                            <i class="fas fa-plus-circle me-1"></i>
                            Puedes crear nuevas acciones automáticas que se ejecutarán según el evento seleccionado.
                        </small><br>

                        <small>
                            <i class="fas fa-edit me-1"></i>
                            Es posible modificar acciones existentes para ajustar su configuración o comportamiento.
                        </small><br>

                        <small>
                            <i class="fas fa-trash me-1"></i>
                            Puedes eliminar acciones que ya no sean necesarias dentro de la regla.
                        </small><br>

                        <small>
                            <i class="fas fa-eye me-1"></i>
                            Puedes visualizar el detalle completo de cada acción antes de editarla o eliminarla.
                        </small><br>





                    </div>

                </div>
            </div>
        </div>





        @csrf
        @method('PUT')
        @include('form_logic._form')

        <input type="hidden" name="acciones_json" id="acciones-json"
            value='@json(old("acciones_json", $rule->acciones ?? []))'>




    </form>


    @include('form_logic.logica', [$isEdit = true])

@endsection