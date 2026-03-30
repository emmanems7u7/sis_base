@include('formularios.partials.accion_eliminar_masivo')

<div class="table-responsive d-md-block mt-3">
    <table class="table table-bordered table-striped mt-3">
        <!-- Fila de controles (activar selección y botón eliminar) -->
        <thead>


            <!-- Cabeceras reales de la tabla -->
            <tr class="table-dark">
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
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>

        @include('formularios.partials.iterador_tabla')
    </table>
</div>