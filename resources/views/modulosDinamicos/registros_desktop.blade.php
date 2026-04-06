@php
    $formulario = $item['formulario'];
    $respuestas = $item['respuestas'];
    $totalCols = $formulario->campos->count() + 5; // Para colspan
@endphp
@include('formularios.partials.accion_eliminar_masivo')


<div class="card shadow-lg mt-3" data-formulario-id="{{ $formulario->id }}">
    <div class="card-body">

        <h5 class="mt-4"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h5>

        @include('formularios.partials.iterador_tabla', [
            'formulario' => $formulario,
            'respuestas' => $respuestas,
            'totalCols' => $totalCols,
            'modulo' => $modulo->id,
            'mostrarModal' => true,
            'mostrarAcciones' => true
        ])
      

    </div>
</div>
