

    
    <div class="row g-3 mt-2"> {{-- g-3 agrega espacio entre columnas --}}
        @foreach($formulariosConRespuestas as $item)
        <div class="card shadow-lg mt-3">
        <div class="card-body">

            @php
                $formulario = $item['formulario'];
                $respuestas = $item['respuestas'];
            @endphp

            <h5 class="mt-4"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h5>

            @include('formularios.modal_busqueda' , ['formulario' => $formulario, 'campos' => $formulario->campos, 'modulo' => $modulo->id])
            
            @include('modulosDinamicos.botones_accion', ['formulario' => $formulario])
            
            @forelse($respuestas as $respuesta)
                <div class="col-12 col-md-6 col-lg-4 mt-2"> {{-- RESPONSIVE COLUMNS --}}
                    <div class="card h-100 shadow-sm">
                        <div class="card-body" style="max-height:400px; overflow-y:auto; position:relative;">
                            <h6 class="card-title">{{ $respuesta->actor->name ?? 'Anónimo' }}</h6>
                            <p class="text-muted mb-2">Fecha de registro: {{ $respuesta->created_at->format('d/m/Y H:i') }}</p>

                            @foreach($formulario->campos->sortBy('posicion') as $campo)
                                @php
                                    $valores = $respuesta->camposRespuestas
                                        ->where('cf_id', $campo->id)
                                        ->pluck('valor')
                                        ->toArray();
                                    $tipoCampo = strtolower($campo->campo_nombre);
                                @endphp
                                <div class="mb-2">
                                    <strong>{{ $campo->etiqueta }}:</strong>
                                    @foreach($valores as $v)
                                        @switch($tipoCampo)
                                            @case('checkbox')
                                            @case('radio')
                                            @case('selector')
                                                {{ $campo->opciones_catalogo->where('catalogo_codigo', $v)->first()?->catalogo_descripcion }}
                                                @if(!$loop->last), @endif
                                            @break

                                            @case('imagen')
                                                <img src="{{ asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") }}"
                                                     style="max-width:100px; max-height:100px;"
                                                     class="rounded me-1 mb-1">
                                            @break

                                            @case('video')
                                                <video src="{{ asset("archivos/formulario_{$formulario->id}/videos/{$v}") }}"
                                                       style="max-width:100%; height:auto;" controls class="mb-1"></video>
                                            @break

                                            @case('archivo')
                                                <a href="{{ asset("archivos/formulario_{$formulario->id}/archivos/{$v}") }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary mb-1">Descargar</a>
                                            @break

                                            @case('enlace')
                                                <a href="{{ $v }}" target="_blank">Ver enlace</a>
                                            @break

                                            @case('fecha')
                                                {{ \Carbon\Carbon::parse($v)->format('d/m/Y') }}
                                            @break

                                            @case('hora')
                                                {{ $v }}
                                            @break

                                            @default
                                                {{ $v }}
                                        @endswitch
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div class="card-footer d-flex justify-content-start flex-wrap">
                            <a href="{{ route('respuestas.edit', $respuesta) }}" class="btn btn-sm btn-warning me-1 mb-1">
                                <i class="fas fa-pencil-alt"></i> Editar
                            </a>
                            <a href="#" class="btn btn-sm btn-danger mb-1"
                               onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No hay respuestas registradas para este formulario.</p>
            @endforelse

            </div></div> 
        @endforeach
    </div>
<div class="table-responsive">
   {{-- Paginación centrada --}}
   <div class="d-flex justify-content-center mt-3">
        {{ $respuestas->links('pagination::bootstrap-4') }}
    </div>
</div>
 


