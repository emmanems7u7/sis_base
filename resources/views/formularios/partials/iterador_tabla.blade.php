<div class="table-responsive {{ $responsiveClass ?? '' }} mt-3">

    {{-- Modal opcional --}}
    @if(!empty($mostrarModal))
        @include('formularios.modal_busqueda', [
            'formulario' => $formulario,
            'campos' => $formulario->campos,
            'modulo' => $modulo ?? null
        ])
    @endif

    <table class="table table-bordered table-striped mt-3">
        
        {{-- HEADER SUPERIOR OPCIONAL --}}
        @if(!empty($mostrarAcciones))
            <thead class="table-dark">
                <tr>
                    <th colspan="{{ $totalCols }}">
                        <div class="d-flex justify-content-between flex-wrap align-items-center">
                            @include('formularios.partials.botones_accion', ['formulario' => $formulario])
                        </div>
                    </th>
                </tr>
            </thead>
        @endif

    {{-- CABECERA PRINCIPAL --}}
    <thead class="table-dark">
           <tr>
                @can($formulario->id . '.eliminar')
                    <th class="check-col_{{ $formulario->id }} d-none">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input p-0" type="checkbox"
                                    id="check-todos_{{ $formulario->id }}">
                                <label class="form-check-label text-white">Todos</label>
                            </div>
                        </th>
                @endcan
                <th>#</th>

                @if($formulario->config['mostrar_usuario'] ?? false)
                    <th>Quién llenó</th>
                @endif

                @foreach($formulario->campos->sortBy('posicion') as $campo)
                    <th>{{ $campo->etiqueta }}</th>
                @endforeach

                @if($formulario->config['mostrar_fecha'] ?? false)
                    <th>Fecha Registro</th>
                @endif

                <th>Acciones</th>
            </tr>
        </thead>

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
            @if($formulario->config['mostrar_usuario'] ?? false)
                <td>{{ $respuesta->actor->name ?? 'Anonimo' }}</td>
            @endif
            @foreach($formulario->campos->sortBy('posicion') as $campo)
                @include('formularios.partials.iterador_componentes')

            @endforeach
            @if($formulario->config['mostrar_fecha'] ?? false)
                <td>{{ $respuesta->created_at->format('d/m/Y H:i') }}</td>
            @endif
            <td>
                @include('formularios.partials.Botones', ['modulo' => $modulo])
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

    </table>

    {{-- PAGINACIÓN --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>

</div>