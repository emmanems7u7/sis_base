<div class="row">
    <div class="col-md-6 ">
        <div class="card shadow-lg mb-2">
            <div class="card-body">
                <h5 class="mb-2">{{ $formulario->nombre }}</h5>
                <a href="{{ route('formularios.index') }}" class="btn btn-secondary btn-sm"><i
                        class="fas fa-arrow-left me-1"></i>Volver a Formularios</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 ">
        <div class="card shadow-lg ">
            <div class="card-body">
                <p class="text-muted">{{ $formulario->descripcion }}</p>
            </div>
        </div>
    </div>
</div>