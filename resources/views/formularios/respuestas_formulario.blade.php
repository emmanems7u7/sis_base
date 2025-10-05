@extends('layouts.argon')

@section('content')


<div class="row">
    <div class="col-md-6 mt-2 order-2 order-md-1">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Respuestas del Formulario: {{ $formulario->nombre }}</h5>
                <a href="{{ route('formularios.index') }}" class="btn btn-sm btn-secondary "><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ route('formularios.registrar', $formulario) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Registrar
                </a>
                <div class="btn-group" role="group" aria-label="Export options">
               
                <div class="btn-group" role="group">
                    <button id="btnGroupExport" type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btnGroupExport">
                        <li>
                            <a class="dropdown-item" target="_blank" href="{{ route('formularios.exportPdf', $formulario) }}">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" target="_blank" href="{{ route('formularios.exportExcel', $formulario) }}">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


                <div class="row mb-2">
    <div class="col-md-12">
        <form action="{{ route('formularios.respuestas.formulario', $formulario->id) }}" method="GET">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre" value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mt-2 order-1 order-md-2">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Información sobre el formulario</h5>
                <p class="text-muted">{{ $formulario->descripcion }}</p>
            </div>
        </div>
    </div>
</div>


    {{-- Tabla para pantallas grandes --}}
    <div class="table-responsive d-none d-md-block mt-3">
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Quién llenó</th>
                    @foreach($formulario->campos->sortBy('posicion') as $campo)
                        <th>{{ $campo->etiqueta }}</th>
                    @endforeach
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($respuestas as $respuesta)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $respuesta->actor->name ?? 'Anonimo' }}</td>

                        @foreach($formulario->campos->sortBy('posicion') as $campo)
                            @php
                                $valores = $respuesta->camposRespuestas
                                    ->where('cf_id', $campo->id)
                                    ->pluck('valor')
                                    ->toArray();
                                $tipoCampo = strtolower($campo->campo_nombre);
                                $displayValores = [];
                                foreach($valores as $v) {
                                    switch($tipoCampo) {
                                        case 'checkbox':
                                        case 'radio':
                                        case 'selector':
                                            $desc = $campo->opciones_catalogo->where('catalogo_codigo', $v)->first()?->catalogo_descripcion;
                                            $displayValores[] = $desc ?? $v;
                                            break;

                                        case 'imagen':
                                            $displayValores[] = "<img src='".asset("archivos/formulario_{$formulario->id}/imagenes/{$v}")."' style='max-width:50px; max-height:50px;' class='rounded me-1 mb-1'>";
                                            break;

                                        case 'video':
                                            $displayValores[] = "<video src='".asset("archivos/formulario_{$formulario->id}/videos/{$v}")."' style='max-width:100px; max-height:50px;' controls></video>";
                                            break;

                                        case 'archivo':
                                            $displayValores[] = "<a href='".asset("archivos/formulario_{$formulario->id}/archivos/{$v}")."' target='_blank' class='btn btn-sm btn-outline-primary mb-1'>Descargar</a>";
                                            break;

                                        case 'enlace':
                                            $displayValores[] = "<a href='$v' target='_blank'>Ver enlace</a>";
                                            break;

                                        case 'fecha':
                                            $displayValores[] = \Carbon\Carbon::parse($v)->format('d/m/Y');
                                            break;

                                        case 'hora':
                                            $displayValores[] = $v;
                                            break;

                                        default: // Text, Textarea, Number, Email, Password, Color
                                            $displayValores[] = $v;
                                    }
                                }
                            @endphp
                            <td>{!! implode(' ', $displayValores) !!}</td>
                        @endforeach

                        <td>{{ $respuesta->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('respuestas.edit', $respuesta) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-pencil-alt"></i> Editar
                            </a>

                            <a href="#" class="btn btn-sm btn-danger"
                               onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>

                            <form id="eliminarRespuesta_{{ $respuesta->id }}" method="POST"
                                  action="{{ route('respuestas.destroy', $respuesta) }}" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $formulario->campos->count() + 4 }}" class="text-center">
                            No hay respuestas registradas para este formulario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cards para móviles --}}
    <div class="d-md-none mt-2">
        @forelse($respuestas as $respuesta)
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
                    <a href="{{ route('respuestas.edit', $respuesta) }}" class="btn btn-sm btn-warning me-1">
                        <i class="fas fa-pencil-alt"></i> Editar
                    </a>
                    <a href="#" class="btn btn-sm btn-danger"
                       onclick="confirmarEliminacion('eliminarRespuesta_{{ $respuesta->id }}', '¿Estás seguro de que deseas eliminar esta respuesta?')">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center">No hay respuestas registradas para este formulario.</p>
        @endforelse
    </div>
    
    <div class="d-flex justify-content-center mt-2">
    {{ $respuestas->links('pagination::bootstrap-4') }}
</div>


@endsection
