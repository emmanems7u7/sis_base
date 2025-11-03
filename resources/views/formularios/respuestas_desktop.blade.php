<div class="table-responsive d-md-block mt-3">
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
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
                                    case 'checkbox':
                                    case 'radio':
                                    case 'selector':
                                        $desc = $campo->opciones_catalogo->where('catalogo_codigo', $v)->first()?->catalogo_descripcion;
                                        $displayValores[] = $desc ?? $v;
                                        break;

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

                                    default: // Text, Textarea, Number, Email, Password, Color
                                        $displayValores[] = $v;
                                }
                            }
                        @endphp
                        <td>{!! implode(' ', $displayValores) !!}</td>
                    @endforeach

                    <td>{{ $respuesta->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('respuestas.edit', $respuesta) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-pencil-alt"></i> Editar
                        </a>

                        <a href="#" class="btn btn-sm btn-danger"
                            onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </a>

                        <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST"
                            action="{{ route('respuestas.destroy', $respuesta) }}" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $formulario->campos->count() + 4 }}" class="text-center">
                        No hay respuestas registradas para este formulario.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>