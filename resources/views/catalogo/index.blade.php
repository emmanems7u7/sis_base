@extends('layouts.argon')

@section('content')

<style>
    .small-table {
    font-size: 0.9rem;
}

.small-table th,
.small-table td {
    padding: 0.35rem 0.5rem; 
}

.small-table .badge {
    font-size: 0.7rem;
}

.th-dependencia {
    width: 25px !important;
    white-space: nowrap; /* evita que el contenido haga crecer la columna */
}

.th-codigo {
    width: 25px !important;
    white-space: nowrap; /* evita que el contenido haga crecer la columna */
}

</style>


<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg ">
            <div class="card-body ">
                <h5>Administración de Catalogos y Categorías</h5>
                    

                        <a href="{{ route('home') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                        <a href="{{ route('categorias.create') }}" class="btn btn-sm btn-primary">{{ __('ui.new_f_text') }}
                            {{ __('lo.categoria') }}</a>

                        <a href="{{ route('catalogos.create') }}" class="btn btn-sm btn-primary">{{ __('ui.new_text') }}
                            {{ __('lo.catalogo') }}</a>
                   


            </div>

        </div>
    </div>
    <div class="col-md-6">

<div class="card">
    <div class="card-body">

<small><i class="fas fa-info-circle me-1"></i>En este módulo puedes crear, organizar y administrar los catálogos de tu sistema, asociándolos a sus respectivas categorías para un manejo más eficiente.</small><br>

<small><i class="fas fa-layer-group me-1"></i>Los catálogos se agrupan por categorías, lo que facilita la búsqueda, filtrado y organización de los registros dentro del sistema.</small><br>


    </div>
</div>
    </div>
</div>
    

    <div class="row">

        <div class="col-md-3 mt-2">
           @include('catalogo.lista_categorias')
        </div>

        <div class="col-md-9  mt-2">
          @include('catalogo.lista_catalogo')
        </div>

    </div>

   

@endsection