<div class="tab-pane fade show active" id="content-concatenado" role="tabpanel" aria-labelledby="tab-concatenado">
                <div class="row mb-3">
                    <!-- Columna Campos -->
                    <div class="col-md-6">
                        <h6>Campos disponibles</h6>
                        <div id="camposDisponibles" class="d-flex flex-wrap gap-1">
                            @foreach($campos as $campo)
                                <div class="campo-item badge bg-info text-white px-2 py-1 small" 
                                    data-id="{{ $campo->id }}" draggable="true" style="cursor: grab; font-size: 0.7rem;">
                                    {{ $campo->etiqueta }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Columna Separadores -->
                    <div class="col-md-6">
                        <h6>Separadores</h6>
                        <div id="separadoresDisponibles" class="d-flex flex-wrap gap-1">
                            @php
                                $separadores = [' ', '-', '_', '(', ')', '[', ']', '.', ',', '|', '/'];
                            @endphp
                            @foreach($separadores as $sep)
                                <div class="separador-item badge bg-warning text-white px-2 py-1 small" 
                                    data-sep="{{ $sep }}" draggable="true" style="cursor: grab; font-size: 0.7rem;">
                                    {{ $sep === ' ' ? '[espacio]' : $sep }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Constructor -->
                <div id="constructorConcatenado" class="border p-2 mb-2" style="min-height:50px; border-radius:0.35rem; background-color:#f8f9fa;">
                    <small id="textoAyuda" class="text-muted">Arrastra aquí los campos y separadores</small>
                </div>

                <!-- Guardar -->
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-success" id="guardarConcatenado">
                        Guardar
                    </button>
                </div>
            </div>
