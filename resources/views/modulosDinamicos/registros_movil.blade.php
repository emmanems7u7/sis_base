@php
    $formulario = $item['formulario'];
    $respuestas = $item['respuestas'];
@endphp

<div class="mb-3">
    <h5 class="mb-2"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h5>

    @include('formularios.modal_busqueda', ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => $modulo->id])
    @include('modulosDinamicos.botones_accion', ['formulario' => $formulario])

    @if($respuestas->count() > 0)
        <div class="row g-3">
            @foreach($respuestas as $respuesta)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header p-2">
                            <strong>{{ $respuesta->actor->name ?? 'Anónimo' }}</strong>
                            <span class="text-muted small">({{ $respuesta->created_at->format('d/m/Y H:i') }})</span>
                        </div>
                        <div class="card-body" style="max-height:400px; overflow-y:auto;">
                            <ul class="list-group list-group-flush">
                                @foreach($formulario->campos->sortBy('posicion') as $campo)
                                    @php
                                        $valores = $respuesta->camposRespuestas
                                            ->where('cf_id', $campo->id)
                                            ->pluck('valor')
                                            ->toArray();

                                        $tipoCampo = strtolower($campo->campo_nombre);
                                        $displayValores = [];

                                        foreach ($valores as $v) {

                                            switch ($tipoCampo) {

                                                case 'checkbox':
                                                case 'radio':
                                                case 'selector':
                                                    $descripcion = $campo->opciones_catalogo
                                                        ->where('catalogo_codigo', $v)
                                                        ->first()?->catalogo_descripcion;

                                                    if ($descripcion) {
                                                        $displayValores[] = e($descripcion);
                                                    }
                                                    break;

                                                case 'imagen':
                                                    $displayValores[] = "<img src='" . asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") . "' 
                                                    style='max-width:80px; max-height:80px;' 
                                                    class='rounded me-1 mb-1'>";
                                                    break;

                                                case 'video':
                                                    $displayValores[] = "<video src='" . asset("archivos/formulario_{$formulario->id}/videos/{$v}") . "' 
                                                    style='max-width:100%; height:auto;' 
                                                    controls 
                                                    class='mb-1'></video>";
                                                    break;

                                                case 'archivo':
                                                    $displayValores[] = "<a href='" . asset("archivos/formulario_{$formulario->id}/archivos/{$v}") . "' 
                                                    target='_blank' 
                                                    class='btn btn-sm btn-outline-primary mb-1'>
                                                    Descargar
                                                </a>";
                                                    break;

                                                case 'enlace':
                                                    $displayValores[] = "<a href='{$v}' target='_blank'>Ver enlace</a>";
                                                    break;

                                                case 'fecha':
                                                    $displayValores[] = \Carbon\Carbon::parse($v)->format('d/m/Y');
                                                    break;

                                                case 'hora':
                                                    $displayValores[] = $v;
                                                    break;

                                                default:
                                                    $displayValores[] = e($v);
                                            }
                                        }
                                    @endphp

                                    <li class="list-group-item py-1">
                                        <strong>{{ $campo->etiqueta }}:</strong><br>
                                        {!! implode(', ', $displayValores) !!}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer d-flex justify-content-start flex-wrap">
                            @include('formularios.partials.Botones', ['modulo' => $modulo->id])

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación centrada --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $respuestas->links('pagination::bootstrap-4') }}
        </div>

    @else
        <p class="text-muted">No hay respuestas registradas para este formulario.</p>
    @endif
</div>