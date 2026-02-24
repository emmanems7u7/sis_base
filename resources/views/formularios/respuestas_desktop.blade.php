<div class="table-responsive d-md-block mt-3">
    <table class="table table-bordered table-striped mt-3">
        <!-- Fila de controles (activar selección y botón eliminar) -->
        <thead>
            <tr>
                @can($formulario->id . '.eliminar')
                    <th colspan="{{ $formulario->campos->count() + 6 }}" class="bg-dark">
                        <div class="d-flex justify-content-between align-items-center py-0" style="font-size:0.85rem;">
                           


                            <button data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar registro"
                                id="btn-eliminar-masivo" class="d-none btn btn-danger btn-sm text-white"
                                onclick="confirmarEliminacion('form-eliminar-masivo', '¿Estás seguro de que deseas estas respuestas?, la acción no puede deshacerse.')"
                                disabled>
                                <i class="fas fa-trash-alt"></i>
                            </button>

                            <form id="form-eliminar-masivo" method="POST" action="{{ route('respuestas.eliminarMasivo') }}"
                                style="display:none;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="respuestas_ids" id="respuestas_ids">
                            </form>


                        </div>
                    </th>
                @endcan
            </tr>

            <!-- Cabeceras reales de la tabla -->
            <tr class="table-dark">
                @can($formulario->id . '.eliminar')
                    <th class="check-col d-none">

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input p-0 " type="checkbox" id="check-todos"
                                style="width:16px; height:16px;">
                            <label class="form-check-label mb-0 text-white" for="check-todos">Todos</label>
                        </div>


                    </th>
                @endcan


                <th>#</th>
                <th>Quién llenó</th>
                @foreach($formulario->campos->sortBy('posicion') as $campo)
                    <th>{{ $campo->etiqueta }}</th>
                @endforeach
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            @forelse($respuestas as $respuesta)
                <tr>
                    @can($formulario->id . '.eliminar')

                        <td class="check-col d-none">


                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input p-0 fila-checkbox" type="checkbox"
                                    value="{{ $respuesta->id }}" id="seleccion_{{ $respuesta->id }}" style="width:16px; height:16px;">
                                <label class="form-check-label mb-0" for="seleccion_{{ $respuesta->id }}">Seleccion</label>
                            </div>


                        </td>
                    @endcan
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $respuesta->actor->name ?? 'Anonimo' }}</td>

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
                                        $displayValores[] = "<img src='" . asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") . "' style='max-width:50px; max-height:50px;' class='rounded me-1 mb-1'>";
                                        break;
                                    case 'video':
                                        $displayValores[] = "<video src='" . asset("archivos/formulario_{$formulario->id}/videos/{$v}") . "' style='max-width:100px; max-height:50px;' controls></video>";
                                        break;
                                    case 'archivo':
                                        $displayValores[] = "<a href='" . asset("archivos/formulario_{$formulario->id}/archivos/{$v}") . "' target='_blank' class='btn btn-sm btn-outline-primary mb-1'>Descargar</a>";
                                        break;
                                    case 'enlace':
                                        $displayValores[] = "<a href='$v' target='_blank'>Ver enlace</a>";
                                        break;
                                    case 'fecha':
                                        $displayValores[] = \Carbon\Carbon::parse($v)->format('d/m/Y');
                                        break;
                                    case 'hora':
                                        $displayValores[] = $v;
                                        break;
                                    default:
                                        $displayValores[] = $v;
                                }
                            }
                        @endphp
                        <td>{!! implode(' ', $displayValores) !!}</td>
                    @endforeach

                    <td>{{ $respuesta->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @include('formularios.partials.Botones', ['modulo' => 0])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $formulario->campos->count() + 6 }}" class="text-center">
                        No hay respuestas registradas para este formulario.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
