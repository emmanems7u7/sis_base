<h6 class="mb-3">Agrupación de formularios para registros</h6>

<a href="{{ route('grupos.create', $modulo->id) }}" class="btn btn-primary btn-sm mb-3">
    <i class="fas fa-plus"></i> Crear agrupación
</a>
<table class="table table-responsive table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre del Grupo</th>
            <th>Formularios</th>

            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($grupos as $grupo)
            <tr>
                <td>{{ $grupo->id }}</td>
                <td>{{ $grupo->grupo ?? 'Sin nombre' }}</td>
                <td> @foreach ($grupo->formularios_completos as $f)
                    <li>
                        {{ $f['formulario']->nombre }}
                        @if($f['es_principal'])
                            <span class="badge bg-primary">Principal</span>
                        @endif
                    </li>
                @endforeach

                </td>

                <td>
                    <button type="button" class="btn btn-xs btn-info" data-bs-toggle="modal"
                        data-bs-target="#configModal{{ $grupo->id }}">
                        Ver Configuración
                    </button>
                </td>
            </tr>

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
    </tbody>
</table>