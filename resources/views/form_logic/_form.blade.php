@php
    $isEdit = isset($rule);
@endphp

<form method="POST" action="{{ $isEdit ? route('form-logic.update', $rule) : route('form-logic.store') }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Nombre de la Regla</label>
                <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $rule->nombre ?? '') }}"
                    required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Formulario de origen</label>
                <select class="form-select select-formulario" name="formulario_id" required>
                    <option value="">Seleccione...</option>
                    @foreach ($formularios as $form)
                        <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label">Evento</label>
                <select name="evento" class="form-select tipo_valor_principal" required>
                    @php
                        $eventos = ['on_create' => 'Al Crear', 'on_update' => 'Al Actualizar', 'on_delete' => 'Al Eliminar'];
                    @endphp
                    @foreach($eventos as $key => $label)
                        <option value="{{ $key }}" {{ (old('evento', $rule->evento ?? '') == $key) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>



    <div class="form-check mb-3">
        <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ old('activo', $rule->activo ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="activo">Activo</label>
    </div>

    <hr>
    <h5>Acciones</h5>
    <div id="acciones-container"></div>

    <button type="button" class="btn btn-sm btn-primary" id="add-accion">+ Agregar Acción</button>

    <hr>
    <button type="submit" class="btn btn-success">{{ $isEdit ? 'Actualizar' : 'Crear' }} Regla</button>
</form>


<template id="action-template">
    <div class="accion-block border p-3 mb-3 rounded shadow-sm ">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Acción #<span class="accion-numero">__INDEX__</span></strong>
            <button type="button" class="btn btn-sm btn-danger remove-accion">Eliminar</button>
        </div>

        <div class="col-md-6">
            <label>Tipo de Acción</label>
            <select name="actions[__INDEX__][tipo_accion]" class="form-select tipo-accion" required>
                <option value="" disabled selected>Seleccionar tipo de Acción</option>
                <option value="modificar_campo">Modificar Campo</option>

                <option value="enviar_email">Enviar Email</option>
                <option value="crear_registro">Crear Registro</option>
                <option value="notificacion">Notificación</option>
                <!-- puedes añadir más -->
            </select>
        </div>
        <!-- Bloque para enviar email -->
        <div class="email-block d-none">
            <div class="mb-2">
                <label>Para (email)</label>
                <input type="email" name="actions[__INDEX__][email_to]" class="form-control"
                    placeholder="destinatario@ejemplo.com">
            </div>
            <div class="mb-2">
                <label>Asunto</label>
                <input type="text" name="actions[__INDEX__][email_subject]" class="form-control"
                    placeholder="Asunto del correo">
            </div>
            <div class="mb-2">
                <label>Mensaje</label>
                <textarea name="actions[__INDEX__][email_body]" class="form-control" rows="3"
                    placeholder="Mensaje"></textarea>
            </div>
        </div>

        <div class="modificar-campo-block d-none">

            <div class="row g-3">
                <div class="col-md-6">
                    <label>Formulario Destino</label>
                    <select name="actions[__INDEX__][form_ref_id]" class="form-select" required>
                        <option value="">-- Seleccionar Formulario --</option>
                        @foreach($formularios as $form)
                            <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Campo a Modificar</label>
                    <select name="actions[__INDEX__][campo_ref_id]" class="form-select">
                        <option value="">-- Ninguno --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Operación</label>
                    <select name="actions[__INDEX__][operacion]" class="form-select" required>
                        <option value="-1" selected disabled>Seleccione un tipo de operación</option>
                        @foreach ($operaciones as $operacion)

                            <option value="{{ $operacion->catalogo_codigo }}" {{ old('operacion', '-1') == $operacion->catalogo_codigo ? 'selected' : '' }}>
                                {{ $operacion->catalogo_descripcion }}
                            </option>
                        @endforeach


                        @foreach(['sumar', 'restar', 'actualizar', 'copiar', 'asignar'] as $op)
                            <option value="{{ $op }}">{{ ucfirst($op) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label>Valor</label>
                    <div class="input-group">
                        <select name="actions[__INDEX__][tipo_valor]" class="form-select tipo-valor">
                            <option value="static">Valor fijo</option>
                            <option value="campo">Campo del formulario de origen</option>
                        </select>

                        <!-- Input para valor estático -->
                        <input type="text" name="actions[__INDEX__][valor]" class="form-control valor-estatico"
                            placeholder="Valor fijo">

                        <!-- Select para campo del formulario de origen -->
                        <select name="actions[__INDEX__][valor_campo]" class="form-select valor-campo d-none">
                            <option value="">-- Seleccionar campo --</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
        <hr class="my-3">

        <h6>Condiciones
            <button type="button" class="btn btn-sm btn-primary add-condicion">+ Agregar Condición</button>
        </h6>

        <div class="condiciones-container" data-index="__INDEX__"></div>
    </div>
</template>

<template id="condicion-template">
    <div class="condicion-block mb-2 p-2 border rounded">
        <div class="row g-2 align-items-center">
            <!-- Campo de formulario principal -->
            <div class="col-md-4">
                <select name="actions[__INDEX__][conditions][][campo_condicion_origen]"
                    class="form-select cond-form-origen" required>
                    <option value="">-- Seleccione campo origen --</option>
                </select>
            </div>

            <!-- Operador -->
            <div class="col-md-2">
                <select name="actions[__INDEX__][conditions][][operador]" class="form-select" required>
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

            <!-- Campo de formulario destino -->
            <div class="col-md-4">
                <select name="actions[__INDEX__][conditions][][campo_condicion_destino]"
                    class="form-select cond-form-destino" required>
                    <option value="">-- Seleccione campo destino --</option>
                </select>
            </div>

            <!-- Botón eliminar -->
            <div class="col-md-2 d-flex justify-content-center">
                <button type="button" class="btn btn-sm btn-danger remove-condicion">x</button>
            </div>
        </div>
    </div>
</template>

<script>


    document.addEventListener('DOMContentLoaded', () => {
        let accionIndex = {{ $isEdit ? ($rule->actions->count() ?? 0) : 1 }};
        const container = document.getElementById('acciones-container');
        const actionTemplate = document.getElementById('action-template').innerHTML;
        const condicionTemplate = document.getElementById('condicion-template').innerHTML;
        const formularioPrincipal = document.querySelector('.select-formulario');

        // Función genérica para cargar campos de un formulario en un select
        async function cargarCampos(formId, selectElement, placeholder = '-- Seleccione --') {

            if (!formId || !selectElement) return;
            selectElement.innerHTML = `<option value="">Cargando...</option>`;
            try {
                const resp = await fetch(`/formularios/${formId}/obtiene/campos`);
                const campos = await resp.ok ? await resp.json() : [];
                let opciones = `<option value="">${placeholder}</option>`;
                campos.forEach(c => opciones += `<option value="${c.id}">${c.nombre}</option>`);
                selectElement.innerHTML = opciones;
            } catch (err) {
                console.error(err);
                selectElement.innerHTML = '<option value="">Error al cargar campos</option>';
            }


        }

        // Función para actualizar todos los campos dependientes en una acción
        async function actualizarCamposAccion(accionBlock) {

            const formOrigen = formularioPrincipal.value;
            const formDestino = accionBlock.querySelector('select[name^="actions"][name$="[form_ref_id]"]').value;

            // Valor tipo campo
            const valorSelect = accionBlock.querySelector('.valor-campo');
            if (valorSelect && accionBlock.querySelector('.tipo-valor').value === 'campo') {
                await cargarCampos(formOrigen, valorSelect, '-- Seleccione campo origen --');
            }
            // Selector de campo del formulario destino
            const campoDestino = accionBlock.querySelector('select[name^="actions"][name$="[campo_ref_id]"]');
            if (campoDestino) {
                await cargarCampos(formDestino, campoDestino, '-- Ninguno --');
            }
            // Condiciones
            accionBlock.querySelectorAll('.condicion-block').forEach(async cond => {
                const selectOrigen = cond.querySelector('.cond-form-origen');
                const selectDestino = cond.querySelector('.cond-form-destino');
                if (selectOrigen) await cargarCampos(formOrigen, selectOrigen, '-- Seleccione campo origen --');
                if (selectDestino) await cargarCampos(formDestino, selectDestino, '-- Seleccione campo destino --');
            });
        }

        // Agregar acción
        document.getElementById('add-accion').addEventListener('click', () => {
            // Validación campos obligatorios
            const nombreRegla = document.querySelector('input[name="nombre"]').value.trim();
            const formularioOrigen = formularioPrincipal.value;
            const evento = document.querySelector('select[name="evento"]').value;
            if (!nombreRegla) { alertify.error('Ingrese el nombre de la regla'); return; }
            if (!formularioOrigen) { alertify.error('Seleccione el formulario de origen'); return; }
            if (!evento) { alertify.error('Seleccione el evento'); return; }

            // Agregar acción
            const html = actionTemplate.replace(/__INDEX__/g, accionIndex);
            container.insertAdjacentHTML('beforeend', html);
            const accionBlock = container.lastElementChild;
            accionIndex++;

            // Inicializar selects dependientes
            actualizarCamposAccion(accionBlock);
        });

        // Delegación de eventos
        container.addEventListener('change', e => {
            const accionBlock = e.target.closest('.accion-block');

            // Tipo de valor: ocultar/mostrar input o select
            if (e.target.classList.contains('tipo-valor')) {
                const valorInput = accionBlock.querySelector('.valor-estatico');
                const valorSelect = accionBlock.querySelector('.valor-campo');
                if (e.target.value === 'campo') {
                    valorInput.classList.add('d-none');
                    valorSelect.classList.remove('d-none');
                    actualizarCamposAccion(accionBlock);
                } else {
                    valorInput.classList.remove('d-none');
                    valorSelect.classList.add('d-none');
                }
            }

            // Formulario destino: actualizar campos de destino y condiciones
            if (e.target.matches('select[name^="actions"][name$="[form_ref_id]"]')) {
                actualizarCamposAccion(accionBlock);
            }


            // Cambio tipo de acción
            if (e.target.classList.contains('tipo-accion')) {
                const tipo = e.target.value;
                const modificarCampoBlock = accionBlock.querySelector('.modificar-campo-block');
                const emailBlock = accionBlock.querySelector('.email-block');

                // Ocultar todos primero
                if (modificarCampoBlock) modificarCampoBlock.classList.add('d-none');
                if (emailBlock) emailBlock.classList.add('d-none');

                // Mostrar según tipo
                if (tipo === 'modificar_campo' && modificarCampoBlock) modificarCampoBlock.classList.remove('d-none');
                if (tipo === 'enviar_email' && emailBlock) emailBlock.classList.remove('d-none');
            }

        });

        // Cambio de formulario principal
        formularioPrincipal.addEventListener('change', () => {
            container.querySelectorAll('.accion-block').forEach(accionBlock => actualizarCamposAccion(accionBlock));
        });

        // Delegación click: agregar/quitar condiciones y eliminar acción
        document.addEventListener('click', e => {
            const accionBlock = e.target.closest('.accion-block');

            if (e.target.classList.contains('remove-accion')) {
                alertify.confirm(
                    'Eliminar acción',
                    '¿Estás seguro de eliminar esta acción?',
                    function () {
                        // Si confirma
                        accionBlock.remove();
                        alertify.success('Acción eliminada');
                    },
                    function () {
                        // Si cancela
                        alertify.message('Acción no eliminada');
                    }
                );
            }

            if (e.target.classList.contains('add-condicion')) {
                const condiciones = accionBlock.querySelector('.condiciones-container');
                const index = condiciones.dataset.index;
                const html = condicionTemplate.replace(/__INDEX__/g, index);
                condiciones.insertAdjacentHTML('beforeend', html);
                actualizarCamposAccion(accionBlock);
            }

            if (e.target.classList.contains('remove-condicion')) {
                alertify.confirm(
                    'Eliminar acción',
                    '¿Estás seguro de eliminar esta condición?',
                    function () {
                        // Si confirma
                        e.target.closest('.condicion-block').remove();
                        alertify.success('Acción eliminada');
                    },
                    function () {
                        // Si cancela
                        alertify.message('Acción no eliminada');
                    }
                );

            }
        });

        // Reconstruir acciones existentes si estamos editando
        @if($isEdit && $rule->actions->count())
            @foreach($rule->actions as $i => $a)
                const html = actionTemplate.replace(/__INDEX__/g, {{ $i }});
                container.insertAdjacentHTML('beforeend', html);
                const accionBlock = container.lastElementChild;
                actualizarCamposAccion(accionBlock);
            @endforeach
            accionIndex = {{ $rule->actions->count() }};
        @endif
});


</script>