@extends('layouts.argon')

@section('content')


    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <p>{{ __('lo.catalogo') }}</p>
            <div class="row mt-3">
                <div class="col-md-4">
                    <a href="{{ route('categorias.create') }}" class="btn btn-primary">{{ __('ui.new_f_text') }}
                        {{ __('lo.categoria') }}</a>

                    <a href="{{ route('catalogos.create') }}" class="btn btn-primary">{{ __('ui.new_text') }}
                        {{ __('lo.catalogo') }}</a>
                </div>

                <div class="col-md-8">
                    <h5> {{ __('lo.categorias') }} {{ __('ui.availables_text') }}</h5>

                    <div class="accordion" id="accordionCategorias">
                        <div class="row">
                            @foreach($categorias as $index => $categoria)
                                <div class="col-md-4 mb-3">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $index }}">
                                            <button class="accordion-button d-flex justify-content-between align-items-center"
                                                type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                                aria-expanded="false" aria-controls="collapse{{ $index }}">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <span class="me-auto"><strong>{{ $categoria->nombre }}</strong></span>
                                                    <span class="badge bg-{{ $categoria->estado ? 'success' : 'secondary' }}">
                                                        {{ $categoria->estado ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>


                                        <div id="collapse{{ $index }}" class="accordion-collapse collapse"
                                            aria-labelledby="heading{{ $index }}" data-bs-parent="#accordionCategorias">
                                            <div class="accordion-body">
                                                <p><strong>{{ __('ui.description_text') }}:</strong>
                                                    {{ $categoria->descripcion }}</p>

                                                <div class="mt-3">
                                                    <a href="{{ route('categorias.edit', $categoria->id) }}"
                                                        class="btn btn-sm btn-warning"> {!! __('ui.edit_icon') !!} </a>
                                                    <form action="{{ route('categorias.destroy', $categoria->id) }}"
                                                        method="POST" class="d-inline" id="delete-form-{{ $categoria->id }}">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" type="button"
                                                            onclick="confirmarEliminacion('delete-form-{{ $categoria->id }}' , '¿Estás seguro de eliminar esta categoría?')">
                                                            {!! __('ui.delete_icon') !!}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $categorias->links('pagination::bootstrap-4') }}
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body">
            <!-- Formulario de búsqueda -->
            <form action="{{ route('catalogos.index') }}" method="GET" class="mb-3">
                <div class="row justify-content-end">
                    <div class="col-md-6 col-lg-4">
                        <input type="text" name="search" class="form-control" placeholder="Buscar..."
                            value="{{ request()->search }}">
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">{{ __('ui.search_text') }}</button>
                    </div>
                </div>
            </form>

            @if($catalogos->isEmpty())
                <div class="col-12">
                    <div class="alert alert-warning text-center">{{ __('ui.no_item_text') }} {{ __('lo.catalogos') }}.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nº</th>
                                <th scope="col">{{ __('lo.categoria') }}</th>
                                <th scope="col">{{ __('ui.depends_on_text') }}</th>
                                <th scope="col">{{ __('ui.code_text') }}</th>
                                <th scope="col">{{ __('ui.description_text') }}</th>
                                <th scope="col">{{ __('ui.status_text') }}</th>
                                <th scope="col">{{ __('ui.actions_text') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($catalogos as $index => $catalogo)
                                <tr>
                                    <td>{{ $index + 1}}</td>
                                    <td>{{ $catalogo->categoria->nombre }}</td>
                                    <td>{{ $catalogo->catalogo_parent ?? 'No tiene Dependencia'}}</td>
                                    <td>{{ $catalogo->catalogo_codigo }}</td>
                                    <td>{{ $catalogo->catalogo_descripcion }}</td>
                                    <td>
                                        <span class="badge bg-{{ $catalogo->catalogo_estado ? 'success' : 'secondary' }}">
                                            {{ $catalogo->catalogo_estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('catalogos.edit', $catalogo->id) }}"
                                            class="btn btn-sm btn-warning text-white" title="Editar">
                                            {!! __('ui.edit_icon') !!}
                                        </a>
                                        <form action="{{ route('catalogos.destroy', $catalogo->id) }}" method="POST"
                                            id="delete-form-{{ $catalogo->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger"
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

@endsection