<tbody>
    @forelse($respuestas as $respuesta)
        <tr>
            @can($formulario->id . '.eliminar')

                <td class="check-col_{{ $formulario->id }} d-none">


                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input p-0 fila-checkbox_{{ $formulario->id }}" type="checkbox"
                            value="{{ $respuesta->id }}" id="seleccion_{{ $respuesta->id }}" style="width:16px; height:16px;">
                        <label class="form-check-label mb-0" for="seleccion_{{ $respuesta->id }}">Seleccion</label>
                    </div>


                </td>
            @endcan

            <td>{{ $loop->iteration + ($respuestas->currentPage() - 1) * $respuestas->perPage() }}</td>
            <td>{{ $respuesta->actor->name ?? 'Anonimo' }}</td>

            @foreach($formulario->campos->sortBy('posicion') as $campo)
                @include('formularios.partials.iterador_componentes')

            @endforeach

            <td>{{ $respuesta->created_at->format('d/m/Y H:i') }}</td>
            <td>
                @include('formularios.partials.Botones', ['modulo' => 0])
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ $totalCols }}" class="text-center">
                No hay respuestas registradas para este formulario.
            </td>
        </tr>
    @endforelse
</tbody>