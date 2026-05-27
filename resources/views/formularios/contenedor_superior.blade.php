<div class="row">
    <div class="col-md-6 ">
        <div class="card shadow-lg mb-2">
            <div class="card-body">
                <h6 class="mb-1">{{ $formulario->nombre }}</h6>


                @if(!$moduloModelo)

                    <a href="{{ route('formularios.index') }}" class="btn btn-secondary btn-xs"><i
                            class="fas fa-arrow-left me-1"></i>Volver a Formularios</a>
                @else
                    <a href="{{ route('modulo.index', $moduloModelo->id) }}"
                        class="btn btn-secondary btn-xs mt-3">{!! configForm($formulario->id, 'titles.return_modulo') !!}</a>
                @endif

            </div>
        </div>
    </div>

    <div class=" col-md-6">

        <div class="card shadow-lg">

            <div class="card-body ">

                <div>



                    {!! $formulario->descripcion !!}

                </div>

            </div>

        </div>

    </div>
</div>