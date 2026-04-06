@include('formularios.partials.accion_eliminar_masivo')
@include('formularios.partials.iterador_tabla', [
    'formulario' => $formulario,
    'respuestas' => $respuestas,
    'totalCols' => 3 + $formulario->campos->count(),
    'modulo' => 0,
    'responsiveClass' => 'd-md-block',
])