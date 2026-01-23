
<div class="row mt-2">
    
        @forelse($respuestas as $respuesta)
        <div class="col-md-6">
            <div class="card mb-3 shadow-sm">
                <div class="card-body" style="max-height:400px; overflow-y:auto; position:relative;">
                    <h5 class="card-title">{{ $respuesta->actor->name ?? 'Anonimo' }}</h5>
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
                                        <img src="{{ asset("archivos/formulario_{$formulario->id}/imagenes/{$v}") }}" style="max-width:100px; max-height:100px;" class="rounded me-1 mb-1">
                                    @break

                                    @case('video')
                                        <video src="{{ asset("archivos/formulario_{$formulario->id}/videos/{$v}") }}" style="max-width:100%; height:auto;" controls class="mb-1"></video>
                                    @break

                                    @case('archivo')
                                        <a href="{{ asset("archivos/formulario_{$formulario->id}/archivos/{$v}") }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">Descargar</a>
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

                <div class="card-footer">
                    <!-- Botones de acciones -->
                       @include('formularios.partials.Botones',['modulo' => 0])


                </div>
            </div>
            </div>
        @empty
            <p class="text-center">No hay respuestas registradas para este formulario.</p>
        @endforelse
        </div>