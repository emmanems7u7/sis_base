<a href="{{ route('form-logic.create', $modulo) }}" class="btn btn-primary mb-3">Crear nueva regla</a>


@if($isMobile)
    @include('modulos.tab2.movil')
@else
    @include('modulos.tab2.desktop')
@endif