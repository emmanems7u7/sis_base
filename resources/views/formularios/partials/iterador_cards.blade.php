@if($respuestas->count() > 0)
    <div class="row g-3">
        @foreach($respuestas as $respuesta)


        @php
            $rid = $respuesta->id ?? null;
        @endphp

            <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 respuesta-card"
                    data-respuesta-id="{{ $respuesta->id }}"
                    data-form-id="{{ $formulario->id }}">
                    <div class="card-header p-2">
                    @if($formulario->config['mostrar_usuario'] ?? false)

                        <strong>{{ $respuesta->actor->name ?? 'Anónimo' }}</strong>
                    @endif
                    @if($formulario->config['mostrar_fecha'] ?? false)
                        <span class="text-muted small">({{ $respuesta->created_at->format('d/m/Y H:i') }})</span>
                    @endif
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
                </div>
            </div>

       

        @endforeach

       
                
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>

@else
    <p class="text-muted">No hay respuestas registradas para este formulario.</p>
@endif

<div class="offcanvas offcanvas-bottom border-0 rounded-top-4
    {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}"
     tabindex="-1"
     id="offcanvasAcciones"
     style="height: 135px;">

    <div class="offcanvas-header justify-content-center position-relative py-2">

        <div class="text-center w-100">
            <div class="fw-semibold small">
            <i class="fas fa-bolt me-1"></i>  Acciones Disponibles
            </div>

            <div style="width: 100%; height: 2px; background:#dee2e6; margin:6px auto 0; border-radius:10px;"></div>
        </div>

      
    </div>

    <div class="offcanvas-body d-flex justify-content-center align-items-center gap-2 py-2"
         id="accionesContenido">

         <div id="acciones-template" class="d-none">
            @include('formularios.partials.Botones_offcanvas', ['modulo' => $modulo_id])
        </div>
    </div>
</div>


