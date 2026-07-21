<div class="row">
    <div class="col-md-6 ">
        <div class="card shadow-sm mb-2">
            <div class="card-body py-2 px-3 position-relative">

                @if ($isMobile)
                    <i class="fas fa-question-circle position-absolute top-0 end-0 m-2" id="InfoFormulario"
                        style="cursor:pointer; font-size:18px;">
                    </i>
                @endif

                <h6 class="mb-1">{{ $formulario->nombre }}</h6>

                @if (!$moduloModelo)
                    <a href="{{ route('formularios.index') }}" class="btn btn-secondary btn-sm mt-1">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Formularios
                    </a>
                @else
                    <a href="{{ route('modulo.index', $moduloModelo->id) }}" class="btn btn-secondary btn-sm mt-1">
                        {!! configForm($formulario->id, 'titles.return_modulo') !!}
                    </a>
                @endif

            </div>
        </div>
    </div>

    @if (!$isMobile)
        <div class=" col-md-6">
            <div class="card shadow-lg">
                <div class="card-body ">
                    {!! $formulario->descripcion !!}
                </div>
            </div>
        </div>
    @endif
</div>
@if ($isMobile)
    <script>
        tippy('#InfoFormulario', {
            content: `
        <div class="{{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }} p-2 rounded">
            {!! $formulario->descripcion !!}
        </div>
    `,
            allowHTML: true,
            placement: 'left',
            animation: 'scale',
            interactive: true,
        });
    </script>
@endif
