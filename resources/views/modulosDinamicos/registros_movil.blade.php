@php
    $formulario = $item['formulario'];
    $respuestas = $item['respuestas'];

@endphp
@include('formularios.partials.accion_eliminar_masivo')



<div class="mb-3">
    <h5 class="mb-2"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h5>

    @include('formularios.modal_busqueda', ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => $modulo->id])
    @include('formularios.partials.botones_accion', ['formulario' => $formulario])

    @include('formularios.partials.iterador_cards', ['modulo_id' => $modulo->id])


</div>