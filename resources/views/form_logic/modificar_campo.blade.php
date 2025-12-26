<div id="modal-modificar-campo" class="d-none">
    <div class="row g-3">
        <div class="col-md-6">
            <label>Formulario Destino</label>
            <select id="modal-form-ref" class="form-select">
                <option value="">-- Seleccionar Formulario --</option>
                @foreach($formularios as $form)
                    <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label>Campo a Modificar</label>
            <select id="modal-campo-ref" class="form-select">
                <option value="">-- Ninguno --</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Operación</label>
            <select id="modal-operacion" class="form-select">
                <option value="-1" selected disabled>Seleccione un tipo de operación</option>
                @foreach ($operaciones as $operacion)
                    <option value="{{ $operacion->catalogo_codigo }}">{{ $operacion->catalogo_descripcion }}
                    </option>
                @endforeach

            </select>
        </div>
        <div class="col-md-8">
            <label>Valor</label>
            <div class="input-group">
                <select id="modal-tipo-valor" class="form-select">
                    <option value="static">Valor fijo</option>
                    <option value="campo">Campo del formulario de origen</option>
                </select>
                <input type="text" id="modal-valor-estatico" class="form-control" placeholder="Valor fijo">
                <select id="modal-valor-campo" class="form-select d-none">
                    <option value="">-- Seleccionar campo --</option>
                </select>
            </div>
        </div>
    </div>
</div>