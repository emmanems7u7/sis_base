<div class="card">
    <div class="card-body">
        <form action="{{ route('catalogos.index') }}" method="GET" class="mb-3">
            <div class="d-flex justify-content-end">
                <div class="w-100">
                    <div class="input-group">
                        <input type="text" name="search_c" class="form-control" placeholder="Buscar..."
                            value="{{ request('search_c') }}">

                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>
                            {{ __('ui.search_text') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
        @foreach($categorias as $categoria)
            <div class="card h-100 shadow-lg small mt-2 card_small">

                <div class="card-body py-2 d-flex justify-content-between">

                    <div>

                        <div class="mt-1">
                            <span class="badge bg-{{ $categoria->estado ? 'success' : 'secondary' }}">
                                {{ $categoria->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        @if(!empty($categoria->descripcion))
                            <div class="mt-1">

                            </div>
                        @endif
                        <div>
                            <strong> <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                    title="{{ $categoria->descripcion }}">
                                </i> {{ $categoria->nombre }}</strong>
                        </div>




                    </div>

                    {{-- DERECHA: Acciones --}}
                    <div class="d-flex flex-column">
                        <a href="{{ route('categorias.edit', $categoria->id) }}" class="btn btn-xs btn-warning mb-1"
                            title="Editar">
                            {!! __('ui.edit_icon') !!}
                        </a>

                        <form action="{{ route('categorias.destroy', $categoria->id) }}" method="POST"
                            id="delete-form-{{ $categoria->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-xs btn-danger" title="Eliminar" onclick="confirmarEliminacion(
                        'delete-form-{{ $categoria->id }}',
                        '¿Estás seguro de eliminar esta categoría?'
                    )">
                                {!! __('ui.delete_icon') !!}
                            </button>
                        </form>
                    </div>


                </div>
            </div>
        @endforeach


        <div class="d-flex justify-content-center mt-2">
            {{ $categorias->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>