@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <a href="{{ route('widgets.create') }}" class="btn btn-success">+ Crear Widget</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($widgets as $widget)
                            <tr>
                                <td>{{ $widget->nombre }}</td>
                                <td>{{ $widget->tipoNombre() }}</td>
                                <td>
                                    @if($widget->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <!-- Editar -->
                                    <a href="{{ route('widgets.edit', $widget->id ?? '#') }}"
                                        class="btn btn-sm btn-warning">Editar</a>

                                    <!-- Eliminar -->
                                    <a href="javascript:void(0)" class="btn btn-sm btn-danger"
                                        onclick="confirmarEliminacion('eliminarWidgetForm_{{ $widget->id }}', '¿Estás seguro de eliminar este widget?')">
                                        Eliminar
                                    </a>
                                    <form id="eliminarWidgetForm_{{ $widget->id }}"
                                        action="{{ route('widgets.destroy', $widget->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay widgets creados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection