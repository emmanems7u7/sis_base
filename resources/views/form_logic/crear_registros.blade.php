<div id="modal-crear-registros" class="d-none">
    <div class="row g-3">
        <!-- Columna izquierda: selector -->
        <div class="col-12 col-md-8">
            <label for="modal-form-ref_crear_registros" class="form-label">Formulario Destino</label>
            <select id="modal-form-ref_crear_registros" class="form-select">
                <option value="">-- Seleccionar Formulario --</option>
                @foreach($formularios as $form)
                    <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- Columna derecha: input + botón + checkbox -->
        <div class="col-12 col-md-4">

            <label for="input-cantidad" class="form-label">Cantidad de registros</label>

            <input type="number" id="input-cantidad" class="form-control" placeholder="Cantidad" min="1">


            <!-- Checkbox para usar relación -->
            <div id="opciones-relacion" class="form-check mt-2" style="display:none;">
                <input type="checkbox" class="form-check-input" id="check-usar-relacion">
                <label class="form-check-label" for="check-usar-relacion">
                    Usar relación
                    <span class="text-warning ms-1" data-bs-toggle="tooltip"
                        title="Al activar esta opción, se bloquearán las filas correspondientes a la relación seleccionada. Se creará un registro por cada fila disponible del campo. Verifique los filtros para evitar registros no deseados.">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </label>
            </div>



        </div>
    </div>
    <!-- Contenedor de formularios relacionados -->
    <div id="formularios-relacionados" class="mt-2" style="display:none;">
        <!-- Aquí se insertan los radios dinámicamente -->
    </div>

    <div id="contenedor-campos-form" class="mt-3"></div>


</div>