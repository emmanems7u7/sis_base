@php
    $formulario = $item['formulario'];
    $respuestas = $item['respuestas'];

@endphp
@include('formularios.partials.accion_eliminar_masivo')



<div class="mb-3">

    @include('formularios.modal_busqueda', ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => $modulo->id])

    <div class="card mt-3 mb-3 shadow-sm">
        <div class="card-body">
            <h6 class="mb-2"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h6>

            @include('formularios.partials.botones_accion', ['formulario' => $formulario])

        </div>
    </div>

    @include('formularios.partials.iterador_cards', ['modulo_id' => $modulo->id])


</div>