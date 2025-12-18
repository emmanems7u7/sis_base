<h5 class="mb-3"><i class="fas fa-cogs me-2 text-success"></i>Gestionar Lógica del Módulo</h5>
<a href="{{ route('form-logic.create', $modulo) }}" class="btn btn-primary mb-3">Crear nueva regla</a>


@if($isMobile)
    @include('modulos.tab_2_mobile')
@else
    @include('modulos.tab_2_desktop')
@endif