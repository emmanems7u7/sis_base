@extends('layouts.argon')

@section('content')



<div class="row">
    <div class="col-md-6 order-2 order-md-1">
        <div class="card shadow-lg">
            <div class="card-body">
            <h5><i class="fas fa-plus-circle"></i> {{ __('ui.create_text') }} {{ __('lo.categoria') }}</h5>
                <a href="{{ route('catalogos.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 order-1 order-md-2">
        <div class="card shadow-lg">
            <div class="card-body">
              
                    <small><i class="fas fa-info-circle me-1"></i>En este módulo puedes crear y administrar categorías para organizar los catálogos del sistema.</small><br>

                    <small><i class="fas fa-layer-group me-1"></i>Las categorías facilitan la búsqueda y clasificación de los catálogos.</small><br>

                    <small><i class="fas fa-pencil-alt me-1"></i>Puedes editar su nombre, descripción y estado de manera rápida.</small>
            </div>
        </div>
    </div>
</div>



    <div class="card shadow-lg mt-2 mb-5">
        <div class="card-body">
            <div class="container">
             
                <form action="{{ route('categorias.store') }}" method="POST">
                    @include('catalogo._formCategoria')
                </form>
            </div>
        </div>
    </div>

@endsection