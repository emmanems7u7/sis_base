<div class="row g-2">

    @foreach($rules as $rule)

            <div class="col-12 col-sm-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100 animacion_card py-1"
                    data-open-offcanvas="offcanvasAccionesReglas" data-rule-id="{{ $rule->id }}">

                    <div class="card-body p-3">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-2">

                            <div class="flex-grow-1 pe-2">

                                <div class="fw-bold small text-truncate">
                                    <i class="fas fa-project-diagram text-primary me-1"></i>
                                    {{ $rule->nombre }}
                                </div>


                            </div>

                            <span class="badge bg-primary rounded-pill">
                                {{ $rule->actions->count() }}
                            </span>

                        </div>

                        <!-- Formulario -->
                        <div class="small mb-2 text-truncate">

                            <i class="fas fa-file-alt text-info me-1"></i>

                            <span class="fw-semibold">
                                {{ $rule->formulario->nombre }}
                            </span>

                        </div>

                        <!-- Evento -->
                        <div class="mb-2">

                            <span class="badge bg-warning text-dark small rounded-pill">
                                {{ $rule->evento }}
                            </span>

                        </div>

                        <!-- Acciones -->
                        @if($rule->actions->count())

                            <div class="d-flex flex-column gap-1">

                                @foreach($rule->actions->take(2) as $act)

                                    <div class="small border rounded-3 px-2 py-1">

                                        <span class="text-primary fw-semibold">
                                            {{ $act->OperacionCatalogo }}
                                        </span>

                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>

                                        <span class="text-muted">
                                            {{ $act->formularioDestino->nombre ?? 'Sin formulario' }}
                                        </span>

                                    </div>

                                @endforeach

                                @if($rule->actions->count() > 2)

                                    <small class="text-muted text-center">
                                        +{{ $rule->actions->count() - 2 }} más
                                    </small>

                                @endif

                            </div>

                        @endif


                        <div class="row w-100 g-2 justify-content-center">
                            <div class="col-4">
                                <a href="{{ route('form-logic.edit', ['rule' => $rule->id, 'modulo' => $modulo->id]) }}"
                                    class="btn btn-xs btn-outline-warning w-100 btn-accion">
                                    <i class="fas fa-pencil-alt me-1"></i>
                                    <br>Editar</a>

                            </div>
                            <div class="col-4">
                                <form action="{{ route('form-logic.delete', $rule->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger w-100 btn-accion">
                                        <i class="fas fa-trash-alt me-1"></i>
                                        <br>Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    @endforeach

</div>

<x-offcanvas-acciones id="offcanvasAccionesReglas" titulo="Acciones Reglas" icono="fas fa-bolt"
    contenidoId="accionesContenidoReglas" templateId="acciones-template-reglas">

    <template id="acciones-template-reglas">
    </template>
</x-offcanvas-acciones>