@extends('layouts.argon')

@section('content')
<div class="row">
    <div class="col-md-6 order-2 order-md-1">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>{!! __('ui.create') !!} {{ __('lo.catalogo') }}</h5>
                <a href="{{ route('catalogos.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 order-1 order-md-2">
        <div class="card shadow-lg">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>En este formulario puedes registrar un nuevo catálogo dentro del sistema, asignándolo a una categoría existente para mantener la información organizada.</small><br>

                <small><i class="fas fa-sitemap me-1"></i>Si el catálogo depende de otro ya existente, puedes seleccionar la dependencia. Si no hay dependencia, el campo puede dejarse vacío.</small><br>

                <small><i class="fas fa-barcode me-1"></i>El <strong>código del catálogo</strong> se genera automáticamente y es de solo lectura, garantizando que no se repitan códigos dentro del sistema.</small><br>

                <small><i class="fas fa-pencil-alt me-1"></i>Puedes agregar el contenido detallado y definir el estado del catálogo para mantener un registro claro y actualizado.</small>
            </div>
        </div>
    </div>
</div>


    <div class="card shadow-lg mt-2 mb-5">
        <div class="card-body">
            <div class="container">
                <form action="{{ route('catalogos.store') }}" method="POST">
                    @include('catalogo._form')
                </form>
            </div>
        </div>
    </div>

@endsection