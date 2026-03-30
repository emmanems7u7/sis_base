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

                        <div class="check-col_{{ $formulario->id }} d-none">

                            <div class="form-check form-check-inline mb-0"
                                style="position:absolute; top:8px; right:8px; z-index:10;">
                                <input class="form-check-input p-0 fila-checkbox_{{ $formulario->id }}" type="checkbox" value="{{ $respuesta->id }}"
                                    id="seleccion_{{ $respuesta->id }}" style="width:16px; height:16px;">
                                <label class="form-check-label mb-0" for="seleccion_{{ $respuesta->id }}">Seleccion</label>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($formulario->campos->sortBy('posicion') as $campo)

                                @include('formularios.partials.iterador_componentes')


                            @endforeach
                        </div>
                    </div>


                    <div class="card-footer">

                        @include('formularios.partials.Botones', ['modulo' => $modulo_id])


                    </div>
                </div>
            </div>

        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>

@else
    <p class="text-muted">No hay respuestas registradas para este formulario.</p>
@endif