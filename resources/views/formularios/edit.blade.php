@extends('layouts.argon')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Editar Formulario</h5>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="formularioTabs" role="tablist">

                <li class="nav-item" role="presentation">

                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
                        type="button" role="tab">

                        <i class="fas fa-cog me-1"></i>
                        Configuración

                    </button>

                </li>

                <li class="nav-item" role="presentation">

                    <button class="nav-link" id="campos-tab" data-bs-toggle="tab" data-bs-target="#campos" type="button"
                        role="tab">

                        <i class="fas fa-list-ul me-1"></i>
                        Campos

                    </button>

                </li>



                <li class="nav-item" role="presentation">

                    <button class="nav-link" id="mensajes-tab" data-bs-toggle="tab" data-bs-target="#mensajes"
                        type="button" role="tab">

                        <i class="fas fa-list-ul me-1"></i>
                        Retorno de mensajes

                    </button>

                </li>

            </ul>



            <div class="tab-content p-4 border border-top-0 rounded-bottom ">

                {{-- Configuración --}}
                <div class="tab-pane fade show active" id="general" role="tabpanel">

                    <form action="{{ route('formularios.update', $formulario) }}" method="POST">

                        @csrf
                        @method('PUT')

                        @include('formularios._form', [
                            'formulario' => $formulario,
                        ])

                    </form>

                </div>

                {{-- Campos --}}
                <div class="tab-pane fade" id="campos" role="tabpanel">

                    @include('formularios.campos.index')

                </div>

                <div class="tab-pane fade" id="mensajes" role="tabpanel">
                    @include('form_configurations.edit')

                </div>
            </div>

        </div>
    </div>
@endsection
