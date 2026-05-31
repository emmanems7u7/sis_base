
@if($respuestas->count() > 0)
    <div class="row g-3">
        @foreach($respuestas as $respuesta)

            <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 animacion_card border-0 overflow-hidden position-relative"
                    data-open-offcanvas="offcanvasAcciones"
                    data-respuesta-id="{{ $respuesta->id }}"
                    data-form-id="{{ $formulario->id }}"
                    data-grupo-id="{{ $respuesta->grupo_id ?? '' }}">
                    <div class="bg-info lateral_card"></div>
                
                    <div class="card-body contenido-ajustado" style="max-height:400px; overflow-y:auto; padding: 0.85rem !important;">

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


                            @if((isset($formulario->config['mostrar_usuario']) && $formulario->config['mostrar_fecha'] ?? false) || (isset($formulario->config['mostrar_usuario']) && $formulario->config['mostrar_fecha'] ?? false))
                                
                                @if($formulario->config['mostrar_usuario'] ?? false)
                                <div class="col-6 col-md-6 mb-1">
                                    <div class="border-bottom pb-1" style="font-size: 0.85rem;">
                                        <strong class="d-block">Usuario:</strong>
                                        <span class="text-muted">
                                        {{ $respuesta->actor->name ?? 'Anónimo' }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($formulario->config['mostrar_fecha'] ?? false)

                                <div class="col-6 col-md-6 mb-1">
                                    <div class="border-bottom pb-1" style="font-size: 0.85rem;">
                                        <strong class="d-block">Fecha:</strong>
                                        <span class="text-muted">
                                        {{ $respuesta->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            

                                @endif

                            <div class="d-none acciones-source">
                                @include('formularios.partials.Botones_offcanvas', [
                                    'modulo' => $modulo_id,
                                    'respuesta' => $respuesta
                                ])
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>

          

        @endforeach



        <div class="d-flex justify-content-center mt-3">
        {{ $respuestas->links('pagination::bootstrap-4') }}
        </div>
    </div>
       

@else
    <p class="text-muted">{!! configForm($formulario->id, 'titles.no_results') !!}</p>
@endif


