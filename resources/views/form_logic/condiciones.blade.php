<div class="mt-2 d-flex gap-2 justify-content-end">


    <select id="selector-condicion-modal" class="form-select form-select-sm w-auto">

        @foreach ($OpcionesCondiciones as $tipo_accion)
            <option value="{{ $tipo_accion->catalogo_codigo }}">{{ $tipo_accion->catalogo_descripcion }}
            </option>
        @endforeach

    </select>

    <button type="button" class="btn btn-sm btn-primary" id="btn-agregar-condicion">
        + Agregar Acción
    </button>

</div>


<div id="condiciones-container"></div>

<template id="condicion-modal-template">
    <div class="condicion-block mb-2 p-2 border rounded">Condición <strong id="num_condicion"></strong>
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select class="form-select cond-form-origen">
                    <option value="">-- Seleccione campo origen --</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select cond-operador">
                    <option value="=">=</option>
                    <option value="!=">!=</option>
                    <option value=">">></option>
                    <option value="<">
                        < </option>
                    <option value=">=">>=</option>
                    <option value="<=">
                        <= </option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select cond-form-destino">
                    <option value="">-- Seleccione campo destino --</option>
                </select>
            </div>
            <div class="col-md-2 d-flex justify-content-center">
                <button type="button" class="btn btn-sm btn-danger remove-condicion-modal">x</button>
            </div>
        </div>
        <div class="mt-2">
            <input type="text" class="form-control cond-mensaje"
                placeholder="Ej: La cantidad ingresada supera el stock disponible">
        </div>
    </div>
</template>




<!-- ========================================= -->
<!-- TEMPLATE VALOR FORM ORIGEN / DESTINO -->
<!-- ========================================= -->
<template id="condicion-form-valor-template">

    <div class="condicion-form-valor-block mb-2 p-2 border rounded">

        Condición <strong class="num-condicion"></strong>

        <div class="row g-2 align-items-center mt-1">

            <!-- formulario -->
            <div class="col-md-3">

                <label class="form-label">
                    Formulario
                </label>

                <select class="form-select cond-tipo-formulario">

                    <option value="">
                        -- Seleccionar --
                    </option>

                    <option value="origen">
                        Formulario Origen
                    </option>

                    <option value="destino">
                        Formulario Destino
                    </option>

                </select>

            </div>

            <!-- campo -->
            <div class="col-md-3">

                <label class="form-label">
                    Campo
                </label>

                <select class="form-select cond-campo">

                    <option value="">
                        -- Seleccione campo --
                    </option>

                </select>

            </div>

            <!-- operador -->
            <div class="col-md-2">

                <label class="form-label">
                    Operador
                </label>

                <select class="form-select cond-operador">

                    <option value="=">=</option>
                    <option value="!=">!=</option>
                    <option value=">">></option>
                    <option value="<">
                        < </option>
                    <option value=">=">>=</option>
                    <option value="<=">
                        <= </option>

                </select>

            </div>

            <!-- valor -->
            <div class="col-md-2">

                <label class="form-label">
                    Valor
                </label>

                <input type="text" class="form-control cond-valor">

            </div>

            <!-- eliminar -->
            <div class="col-md-2 d-flex align-items-end justify-content-center">

                <button type="button" class="btn btn-sm btn-danger remove-condicion-form-valor">

                    x

                </button>

            </div>

        </div>


        <!-- mensaje -->
        <div class="mt-2">

            <input type="text" class="form-control cond-mensaje"
                placeholder="Ej: El valor supera el límite permitido">

        </div>

        <!-- hidden -->
        <input type="hidden" class="condicion-config-hidden">

    </div>

</template>
