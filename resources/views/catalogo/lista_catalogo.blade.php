<div class="card shadow-lg">
    <div class="card-body">



        <form action="{{ route('catalogos.index') }}" method="GET" class="mb-3">
            <div class="d-flex justify-content-end">
                <div class="w-100 w-md-50">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar..."
                            value="{{ request('search') }}">

                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>
                            {{ __('ui.search_text') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if($catalogos->isEmpty())
            <div class="col-12">
                <div class="alert alert-warning text-center">{{ __('ui.no_item_text') }} {{ __('lo.catalogos') }}.</div>
            </div>
        @else
            <div class="table-responsive small-table">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>

                            <th>{{ __('lo.categoria') }}</th>
                            <th class="th-dependencia">Dependencia</th>
                            <th class="th-codigo">{{ __('ui.code_text') }}</th>
                            <th>Contenido</th>
                            <th class="th-codigo">{{ __('ui.status_text') }}</th>
                            <th>{{ __('ui.actions_text') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($catalogos as $index => $catalogo)
                            <tr>

                                <td>{{ $catalogo->categoria->nombre }}</td>
                                <td>
                                    <span class="badge bg-info ">
                                        {{ $catalogo->Dependencia }}
                                    </span>
                                </td>
                                <td>{{ $catalogo->catalogo_codigo }}</td>
                                <td>{{ $catalogo->catalogo_descripcion }}</td>
                                <td>
                                    <span class="badge bg-{{ $catalogo->catalogo_estado ? 'success' : 'secondary' }}">
                                        {{ $catalogo->catalogo_estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('catalogos.edit', $catalogo->id) }}"
                                        class="btn btn-xs btn-warning text-white" title="Editar">
                                        {!! __('ui.edit_icon') !!}
                                    </a>
                                    <form action="{{ route('catalogos.destroy', $catalogo->id) }}" method="POST"
                                        id="delete-form-{{ $catalogo->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-danger"
                                            onclick="confirmarEliminacion('delete-form-{{ $catalogo->id }}' , '¿Estás seguro de eliminar este catalogo?')"
                                            title="Eliminar">
                                            {!! __('ui.delete_icon') !!}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex justify-content-center">
                    {{ $catalogos->links('pagination::bootstrap-4') }}
                </div>
            </div>

        @endif
    </div>
</div>