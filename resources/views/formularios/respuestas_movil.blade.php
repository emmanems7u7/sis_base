@can($formulario->id . '.eliminar')


    <button data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar registro" id="btn-eliminar-masivo"
        class="d-none btn btn-danger btn-sm text-white"
        onclick="confirmarEliminacion('form-eliminar-masivo', '¿Estás seguro de que deseas estas respuestas?, la acción no puede deshacerse.')"
        disabled style="position: fixed; bottom: 70px; right: 20px; z-index: 1050;">
        <i class="fas fa-trash-alt"></i>
    </button>

    <form id="form-eliminar-masivo" method="POST" action="{{ route('respuestas.eliminarMasivo') }}" style="display:none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="respuestas_ids" id="respuestas_ids">
    </form>


@endcan

<div class="row mt-2">

    @can($formulario->id . '.eliminar')
        <div class="card shadow-sm check-col d-none mt-1 mb-1">
            <div class="card-body">
                <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input p-0" type="checkbox" id="check-todos" style="width:16px; height:16px;">
                    <label class="form-check-label mb-0" for="check-todos">Todos</label>
                </div>

            </div>
        </div>
    @endcan

    @forelse($respuestas as $respuesta)
        <div class="col-md-6">
            <div class="card mb-3 shadow-sm">
                <div class="card-body" style="max-height:400px; overflow-y:auto; position:relative;">

                    <div class="check-col d-none">

                        <div class="form-check form-check-inline mb-0"
                            style="position:absolute; top:8px; right:8px; z-index:10;">
                            <input class="form-check-input p-0 fila-checkbox" type="checkbox" value="{{ $respuesta->id }}"
                                id="seleccion_{{ $respuesta->id }}" style="width:16px; height:16px;">
                            <label class="form-check-label mb-0" for="seleccion_{{ $respuesta->id }}">Seleccion</label>
                        </div>
                    </div>
                    <h6 class="card-title">{{ $respuesta->actor->name ?? 'Anonimo' }}</h6>


                    <p class="text-muted mb-2">Fecha de registro: {{ $respuesta->created_at->format('d/m/Y H:i') }}</p>

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

                                    case 'imagen':
                                        $displayValores[] = "<img src='" . asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") . "' 
                                                                                    style='max-width:100px; max-height:100px;' 
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
                                        $displayValores[] = e($v); // escapamos texto normal
                                }
                            }
                        @endphp

                        <div class="mb-2">
                            <strong>{{ $campo->etiqueta }}:</strong>
                            {!! implode(' ', $displayValores) !!}
                        </div>
                    @endforeach
                </div>

                <div class="card-footer">
                    <!-- Botones de acciones -->
                    @include('formularios.partials.Botones', ['modulo' => 0])


                </div>
            </div>
        </div>
    @empty
        <p class="text-center">No hay respuestas registradas para este formulario.</p>
    @endforelse
</div>