<div class="config-relacion" data-relacion='@json($formulario->config['asociado'] ?? null)'>

    <script>
        window.formulariosRef = window.formulariosRef ?? @json($formularios_ref);
    </script>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Formulario relacionado</label>
                <select class="form-select selectFormulario">
                    <option value="">Seleccione un formulario</option>
                    @foreach($formularios_ref as $form)
                        <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Campos del formulario</label>
                <select class="form-select selectCampos">
                    <option value="">Seleccione un campo</option>
                </select>
            </div>
        </div>
    </div>

</div>