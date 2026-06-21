<div id="cont-eliminar-registro" class="d-none">

    <div class="row g-3">

        <!-- FORMULARIO ORIGEN -->
        <div class="col-md-6">
            <label class="form-label small text-muted">
                Formulario origen
            </label>

            <select class="form-select" id="delete-formulario-origen">

                <option value="">
                    --Seleccione--
                </option>

                @foreach ($formularios as $form)
                    <option value="{{ $form->id }}">
                        {{ $form->nombre }}
                    </option>
                @endforeach

            </select>
        </div>

        <!-- FORMULARIO DESTINO -->
        <div class="col-md-6">

            <label>
                Formulario destino
            </label>

            <select class="form-select" id="delete-formulario-destino">

                <option value="">
                    -- Seleccionar --
                </option>

                @foreach ($formularios as $form)
                    <option value="{{ $form->id }}">
                        {{ $form->nombre }}
                    </option>
                @endforeach

            </select>

        </div>


        <div id="delete-condiciones-container"></div>

        <!-- TEMPLATE -->
        <template id="template-delete-condicion">

            <div class="delete-condicion-item border rounded-3 p-3 mb-3 position-relative">

                <!-- ELIMINAR -->
                <button type="button"
                    class="btn btn-xs btn-danger btn-remove-delete-condicion position-absolute top-0 end-0 m-2">

                    <i class="fas fa-trash"></i>

                </button>

                <div class="row g-3 align-items-end">



                    <!-- CAMPO -->
                    <div class="col-md-3">

                        <label>
                            Campo referencia
                        </label>

                        <select class="form-select delete-campo-ref">

                            <option value="">
                                -- Seleccionar --
                            </option>

                        </select>

                    </div>

                    <!-- OPERACION -->
                    <div class="col-md-2">

                        <label>
                            Operación
                        </label>

                        <select class="form-select delete-operacion">

                            <option value="=">
                                Igual
                            </option>

                            <option value="!=">
                                Diferente
                            </option>

                        </select>

                    </div>

                    <!-- VALOR -->
                    <div class="col-md-4">

                        <label>
                            Valor
                        </label>

                        <div class="input-group">

                            <select class="form-select delete-tipo-valor">

                                <option value="static">
                                    Valor fijo
                                </option>

                                <option value="campo">
                                    Campo origen
                                </option>

                            </select>

                            <input type="text" class="form-control delete-valor-estatico" placeholder="Valor fijo">

                            <select class="form-select d-none delete-valor-campo">

                                <option value="">
                                    -- Seleccione --
                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="col-md-12">
                        <label>
                            Mensaje de validación
                        </label>
                        <input type="text" class="form-control cond-mensaje"
                            placeholder="Ej: El valor supera el límite permitido">

                    </div>

                </div>

            </div>
        </template>




        <!-- BOTON -->
        <div class="mt-2">

            <button type="button" class="btn btn-primary" id="btn-add-delete-condicion">

                <i class="fas fa-plus me-1"></i>

                Agregar condición

            </button>

        </div>



        <!-- ALERTA -->
        <div class="col-12">

            <div class="alert alert-danger mb-0">

                <i class="fas fa-trash-alt me-2"></i>

                El registro del formulario destino será eliminado
                si cumple la condición configurada.

            </div>

        </div>

    </div>

</div>
