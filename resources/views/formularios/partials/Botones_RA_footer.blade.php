<div class="d-flex justify-content-center mt-4">

    <div class="row w-100 justify-content-center g-2" style="max-width: 600px;">

        <div class="col-6">
            @if(!$moduloModelo)
                <a href="{{ route('formularios.index') }}" class="btn btn-secondary w-100 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            @else
                <a href="{{ route('modulo.index', $moduloModelo->id) }}" class="btn btn-secondary w-100 py-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            @endif
        </div>

        <div class="col-6">
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="{{ $icono }} me-1"></i> {{$texto}}
            </button>
        </div>

    </div>

</div>