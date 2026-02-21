<a href="{{ route('form-logic.create', $modulo) }}" class="btn btn-primary mb-3">Crear nueva regla</a>


@if($isMobile)
    @include('modulos.tab_2_mobile')
@else
    @include('modulos.tab_2_desktop')
@endif