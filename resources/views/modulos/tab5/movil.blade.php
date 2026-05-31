<h6 class="mb-3">Agrupación de formularios para registros</h6>

<a href="{{ route('grupos.create', $modulo->id) }}" class="btn btn-primary btn-sm mb-3">
    <i class="fas fa-plus"></i> Crear agrupación
</a>

<div class="row g-3">

    @foreach ($grupos as $grupo)

        <div class="col-12">

            <div class="animacion_card card shadow-sm border-0 rounded-4 h-100" data-open-offcanvas="offcanvasAccionesGrupo"
                data-grupo-id="{{ $grupo->id }}">

                <div class="card-body">

                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-2">

                        <div>
                            <div class="fw-bold">
                                {{ $grupo->grupo ?? 'Sin nombre' }}
                            </div>

                        </div>

                        <span class="badge bg-primary rounded-pill">
                            {{ count($grupo->formularios_completos) }}
                        </span>

                    </div>

                    <!-- Formularios -->
                    <div class="mb-3">

                        <div class="fw-semibold small mb-2">
                            Formularios
                        </div>

                        <div class="d-flex flex-column gap-1">

                            @foreach ($grupo->formularios_completos as $f)

                                <div class="d-flex align-items-center gap-2">

                                    <i class="fas fa-file-alt text-primary"></i>

                                    <span>
                                        {{ $f['formulario']->nombre }}
                                    </span>

                                    @if($f['es_principal'])
                                        <span class="badge bg-primary">
                                            Principal
                                        </span>
                                    @endif

                                </div>

                            @endforeach

                        </div>

                    </div>

                    <div class="d-none acciones-source">
                        <div class="row w-100 g-2 justify-content-center">

                            <div class="col-4">
                                <button type="button" class="btn btn-xs btn-outline-info w-100 btn-accion"
                                    data-bs-toggle="modal" data-bs-target="#configModal{{ $grupo->id }}">

                                    <i class="fas fa-cog me-1"></i>
                                    <br>
                                    Config
                                </button>
                            </div>

                            <div class="col-4">
                                <a href="{{ route('grupos.edit', ['grupo' => $grupo->id, 'modulo' => $modulo->id]) }}"
                                    class="btn btn-outline-warning btn-xs w-100 btn-accion">

                                    <i class="fas fa-pencil-alt me-1"></i>
                                    <br>
                                    Editar
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="#" class="btn btn-outline-danger btn-xs w-100 btn-accion"
                                    onclick="confirmarEliminacion('eliminarGrupo_{{ $grupo->id }}', '¿Estás seguro?')">

                                    <i class="fas fa-trash-alt me-1"></i>
                                    <br>
                                    Eliminar
                                </a>

                                <form id="eliminarGrupo_{{ $grupo->id }}" method="POST"
                                    action="{{ route('grupos.destroy', ['grupo' => $grupo->id, 'modulo' => $modulo->id]) }}"
                                    style="display:none;">

                                    @csrf
                                    @method('DELETE')

                                </form>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Modal de configuración del grupo -->
        <div class="modal fade" id="configModal{{ $grupo->id }}" tabindex="-1"
            aria-labelledby="configModalLabel{{ $grupo->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div
                    class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="configModalLabel{{ $grupo->id }}">
                            Configuración del Grupo {{ $grupo->nombre ?? $grupo->id }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">


                        @if ($grupo->config && is_array($grupo->config))

                            @foreach ($grupo->config as $operacion)

                                <div class="card mb-2 shadow-sm">
                                    <div class="card-body">

                                        <strong>
                                            {{ interpretarDestino($operacion['destino']) }}
                                        </strong>

                                        =

                                        <span class="text-primary">
                                            {{ interpretarFormula($operacion) }}
                                        </span>

                                    </div>
                                </div>

                            @endforeach

                        @else
                            <p>No hay configuración disponible.</p>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

</div>


<x-offcanvas-acciones id="offcanvasAccionesGrupo" titulo="Acciones Grupo" icono="fas fa-layer-group"
    contenidoId="accionesContenidoGrupo" templateId="acciones-template-grupo">

    <template id="acciones-template-grupo">

    </template>
</x-offcanvas-acciones>