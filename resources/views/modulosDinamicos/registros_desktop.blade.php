@php
    $formulario = $item['formulario'];
    $respuestas = $item['respuestas'];
    $totalCols = $formulario->campos->count() + 5; // Para colspan
@endphp
@include('formularios.partials.accion_eliminar_masivo')


<div class="card shadow-lg mt-3" data-formulario-id="{{ $formulario->id }}">
    <div class="card-body">

        <h5 class="mt-4"><i class="fas fa-file-alt me-2"></i>{{ $formulario->nombre }}</h5>

        <div class="table-responsive">

            {{-- Modal de búsqueda --}}
            @include('formularios.modal_busqueda', [
                'formulario' => $formulario,
                'campos' => $formulario->campos,
                'modulo' => $modulo->id
            ])

            {{-- Tabla de respuestas --}}
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th colspan="{{ $totalCols }}">
                            <div class="d-flex justify-content-between flex-wrap align-items-center">
                                @include('modulosDinamicos.botones_accion', ['formulario' => $formulario])
                           
                            </div>

                    

                        </th>
                    </tr>
                    

                    <tr>

                    @can($formulario->id . '.eliminar')
                    <th class="check-col_{{ $formulario->id }} d-none">

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input p-0 " type="checkbox" id="check-todos_{{ $formulario->id }}" 
                                style="width:16px; height:16px;">
                            <label class="form-check-label mb-0 text-white" for="check-todos">Todos</label>
                        </div>


                    </th>
                @endcan
                        <th>#</th>
                        <th>Quién llenó</th>
                        @foreach($formulario->campos->sortBy('posicion') as $campo)
                            <th>{{ $campo->etiqueta }}</th>
                        @endforeach
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            @include('formularios.partials.iterador_tabla')
            </table>

        </div>

        {{-- Paginación --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $respuestas->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>
