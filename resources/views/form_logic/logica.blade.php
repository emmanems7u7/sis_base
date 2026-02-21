<script src="https://cdn.ckeditor.com/ckeditor5/38.1.0/classic/ckeditor.js"></script>

<style>
    .btn-xs {
        padding: 2px 6px !important;
        font-size: 11px;
        line-height: 1.2;
        border-radius: 4px;
        margin: 2px;
    }

    #contenedor-campos-form .fa-triangle-exclamation {
        animation: fadeIn 0.3s ease-in-out;
        opacity: 0.9;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    #contenedor-campos-form .fa-triangle-exclamation:hover {
        transform: scale(1.2);
        opacity: 1;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-3px);
        }

        to {
            opacity: 0.9;
            transform: translateY(0);
        }
    }
</style>
<style>
    .campo-btn {
        padding: 2px 6px;
        font-size: 11px;
        line-height: 1.2;
        border-radius: 10px;
        white-space: nowrap;
    }
</style>
<hr>




<!-- Modal Filtrar -->
<div class="modal fade" id="modalFiltrar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="modalFiltrarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFiltrarLabel">Agregar filtros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Formulario relacionado</label>
                        <select id="select-campo-relacion" class="form-select">
                            <option value="">-- Seleccione formulario --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Condición</label>
                        <select id="select-condicion" class="form-select">
                            <option value="">-- Seleccione condición --</option>
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
                        <label>Campo origen</label>
                        <select id="select-campo-origen-filtro" class="form-select">
                            <option value="">-- Seleccione campo --</option>
                        </select>
                    </div>
                </div>
                <!-- Aquí podrías agregar más filtros dinámicos si quieres -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-filtro">Guardar filtro</button>
            </div>
        </div>
    </div>
</div>


<script>
    let editor;

    ClassicEditor
        .create(document.querySelector('#modal-email-body'))
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error(error);
        });
</script>

<script>

    const accionesIniciales = @json(old('acciones_json', $rule->acciones ?? []));

    document.addEventListener('DOMContentLoaded', () => {


        // Objeto para almacenar filtros
        const filtrosGuardados = {};

        let editingIndex = null; // Variable global para saber si estamos editando
        let accionIndex = 0;
        const formularioPrincipal = document.getElementById('formulario_id');
        const accionesList = document.getElementById('acciones-list');
        const accionesJSONInput = document.getElementById('acciones-json');
        const contenedor_botones = document.getElementById('contenedor_botones');
        const contenedor_condiciones = document.getElementById('contenedor_condiciones');
        const contenedor_mensaje = document.getElementById('contenedor_mensaje');


        let accionesArray = [];

        // 🔹 CACHE DE CAMPOS
        const camposCache = { origen: {}, destino: {} };
        let lastFormOrigenId = null;
        let lastFormDestinoId = null;

        // Mostrar/ocultar bloques según tipo de acción
        const tipoAccionSelect = document.getElementById('modal-tipo-accion');
        tipoAccionSelect.addEventListener('change', () => {
            const tipo = tipoAccionSelect.value;

            // Ocultar todos los bloques
            document.getElementById('modal-modificar-campo').classList.add('d-none');
            document.getElementById('modal-email-block').classList.add('d-none');

            // Mostrar según selección
            document.getElementById('modal-modificar-campo')?.classList.add('d-none');
            document.getElementById('modal-email-block')?.classList.add('d-none');
            document.getElementById('modal-crear-registros')?.classList.add('d-none');


            switch (tipo) {
                case 'TAC-001':
                    document.getElementById('modal-modificar-campo').classList.remove('d-none');

                    break;
                case 'TAC-002':
                    break;
                case 'TAC-003':
                    document.getElementById('modal-email-block').classList.remove('d-none');
                    cargarCamposOrigenParaEmail();
                    break;
                case 'TAC-004':
                    break;
                case 'TAC-005':
                    document.getElementById('modal-crear-registros').classList.remove('d-none');

                    break;
            }
            contenedor_mensaje.classList.add('d-none')
            contenedor_condiciones.classList.remove('d-none')
            contenedor_botones.classList.remove('d-none')
        });

        document.getElementById('cancelar-edicion-accion')
            ?.addEventListener('click', function () {

                // Reset índice de edición
                editingIndex = null;

                // Limpiar formulario (ajusta el ID si es diferente)
                document.getElementById('form-accion-modal')?.reset();

                // Ocultar bloques dinámicos
                const bloques = [
                    'modal-modificar-campo',
                    'modal-email-block',
                    'modal-crear-registros'
                ];

                bloques.forEach(id => {
                    document.getElementById(id)?.classList.add('d-none');
                });

                // Restaurar texto del botón principal
                document.getElementById('guardar-accion-modal').textContent = 'Agregar Acción';
                contenedor_botones.classList.add('d-none')
                contenedor_condiciones.classList.add('d-none')
                contenedor_mensaje.classList.remove('d-none')
            });


        // 🔹 Cargar acciones iniciales si estamos editando
        if (accionesIniciales && accionesIniciales.length) {
            accionesArray = accionesIniciales;
            accionesArray.forEach((accion, index) => {
                crearCardVisual(accion, index);
            });
        }

        function cargarCamposCached(formId, selectElement, placeholder = '-- Seleccione --') {
            return new Promise(resolve => {
                if (!formId || !selectElement) return resolve();

                if (camposCache[formId]) {
                    let opciones = `<option value="">${placeholder}</option>`;
                    camposCache[formId].forEach(c => {
                        opciones += `<option value="${c.id}">${c.nombre}</option>`;
                    });
                    selectElement.innerHTML = opciones;
                    return resolve();
                }

                fetch(`/formularios/${formId}/obtiene/campos`)
                    .then(res => res.ok ? res.json() : [])
                    .then(campos => {
                        camposCache[formId] = campos;
                        let opciones = `<option value="">${placeholder}</option>`;
                        campos.forEach(c => {
                            opciones += `<option value="${c.id}">${c.nombre}</option>`;
                        });
                        selectElement.innerHTML = opciones;
                        resolve();
                    })
                    .catch(() => {
                        selectElement.innerHTML = '<option value="">Error</option>';
                        resolve();
                    });
            });
        }


        function cargarCamposConCache(formId, selectElement, tipo = 'origen', placeholder = '-- Seleccione --') {


            if (!formId || !selectElement) return Promise.resolve();
            if (camposCache[tipo][formId]) {
                let opciones = `<option value="">${placeholder}</option>`;
                camposCache[tipo][formId].forEach(c => opciones += `<option value="${c.id}">${c.nombre}</option>`);
                selectElement.innerHTML = opciones;
                return Promise.resolve();
            }
            selectElement.innerHTML = `<option value="">Cargando...</option>`;
            return fetch(`/formularios/${formId}/obtiene/campos`)
                .then(res => res.ok ? res.json() : [])
                .then(campos => {
                    camposCache[tipo][formId] = campos;
                    let opciones = `<option value="">${placeholder}</option>`;
                    campos.forEach(c => opciones += `<option value="${c.id}">${c.nombre}</option>`);
                    selectElement.innerHTML = opciones;
                })
                .catch(() => selectElement.innerHTML = '<option value="">Error</option>');
        }



        async function inicializarModalOptimizado(editData = null) {

            const formOrigenId = formularioPrincipal.value;
            const formDestinoId = editData?.formulario_destino_id || document.getElementById('modal-form-ref').value;

            const promesas = [];

            if (formOrigenId && formOrigenId !== lastFormOrigenId) {
                lastFormOrigenId = formOrigenId;
                promesas.push(cargarCamposConCache(formOrigenId, document.getElementById('modal-valor-campo'), 'origen', '-- Seleccione campo origen --'));


                document.querySelectorAll('#condiciones-modal-container .cond-form-origen')
                    .forEach(sel => promesas.push(cargarCamposConCache(formOrigenId, sel, 'origen', '-- Seleccione campo origen --')));
            }

            // campos destino
            if (formDestinoId && formDestinoId !== lastFormDestinoId) {
                lastFormDestinoId = formDestinoId;
                promesas.push(cargarCamposConCache(formDestinoId, document.getElementById('modal-campo-ref'), 'destino', '-- Seleccione campo destino --'));
                document.querySelectorAll('#condiciones-modal-container .cond-form-destino')
                    .forEach(sel => promesas.push(cargarCamposConCache(formDestinoId, sel, 'destino', '-- Seleccione campo destino --')));
            }

            await Promise.all(promesas);

            // si estamos editando, precargar valores

            if (editData) {
                document.getElementById('modal-tipo-accion').value = editData.tipo;


                if (editData.tipo === 'TAC-001') {
                    document.getElementById('modal-modificar-campo').classList.remove('d-none');
                    document.getElementById('modal-form-ref').value = editData.formulario_destino_id;
                    document.getElementById('modal-campo-ref').value = editData.campo_destino_id;
                    document.getElementById('modal-operacion').value = editData.operacion;
                    document.getElementById('modal-tipo-valor').value = editData.tipo_valor;
                    if (editData.tipo_valor === 'static') {
                        document.getElementById('modal-valor-estatico').value = editData.valor;
                    } else {
                        document.getElementById('modal-valor-campo').value = editData.valor;
                    }
                } else if (editData.tipo === 'enviar_email') {
                    document.getElementById('modal-email-block').classList.remove('d-none');
                    //document.getElementById('modal-email-to').value = editData.to;
                    document.getElementById('modal-email-subject').value = editData.subject;
                    editor.setData(editData.body || '');
                }

                // precargar condiciones
                if (editData.condiciones && editData.condiciones.length) {
                    editData.condiciones.forEach(cond => {
                        agregarCondicionModal(cond);
                    });
                }
            }
        }

        function limpiarModal() {
            document.getElementById('modal-tipo-accion').value = '';
            document.getElementById('modal-modificar-campo').classList.add('d-none');
            document.getElementById('modal-email-block').classList.add('d-none');
            document.getElementById('condiciones-modal-container').innerHTML = '';
            document.getElementById('modal-form-ref').value = '';
            document.getElementById('modal-campo-ref').innerHTML = '<option value="">-- Ninguno --</option>';
            document.getElementById('modal-operacion').value = '-1';
            document.getElementById('modal-tipo-valor').value = 'static';
            document.getElementById('modal-valor-estatico').value = '';
            document.getElementById('modal-valor-campo').innerHTML = '<option value="">-- Seleccionar campo --</option>';
            //document.getElementById('modal-email-to').value = '';
            document.getElementById('modal-email-subject').value = '';

            editor.setData('');

        }

        // Función para agregar condición al modal
        function agregarCondicionModal(condData = null) {
            const template = document.getElementById('condicion-modal-template').content.cloneNode(true);
            const container = template.querySelector('.condicion-block');
            const selectOrigen = container.querySelector('.cond-form-origen');
            const selectDestino = container.querySelector('.cond-form-destino');

            // cargar campos cache
            const formOrigenId = document.querySelector('.select-formulario').value;
            const formDestinoId = document.getElementById('modal-form-ref').value;

            cargarCamposCached(formOrigenId, selectOrigen, '-- Seleccione campo origen --')
                .then(() => {
                    if (condData?.campo_origen_id) selectOrigen.value = condData.campo_origen_id;
                });

            cargarCamposCached(formDestinoId, selectDestino, '-- Seleccione campo destino --')
                .then(() => {
                    if (condData?.campo_destino_id) selectDestino.value = condData.campo_destino_id;
                });

            // precargar operador
            if (condData?.operador) {
                container.querySelector('.cond-operador').value = condData.operador;
            }

            container.querySelector('.remove-condicion-modal')
                .addEventListener('click', () => {
                    alertify.confirm(
                        'Eliminar condición',
                        '¿Estás seguro de que deseas eliminar esta condición?',
                        function () {
                            container.remove();
                            alertify.success('Condición eliminada');
                        },
                        function () {
                            alertify.message('Acción cancelada');
                        }
                    ).set('labels', { ok: 'Sí, eliminar', cancel: 'Cancelar' });
                });
            document.getElementById('condiciones-modal-container').appendChild(container);
        }

        document.getElementById('add-condicion-modal').addEventListener('click', () => agregarCondicionModal());





        //Función para capturar datos del modal
        function capturarDatosModal() {
            const tipoAccion_id = document.getElementById('modal-tipo-accion').value;

            var form_ref_id = document.getElementById('modal-form-ref_crear_registros').value;
            const accionObj = editingIndex !== null
                ? { ...accionesArray[editingIndex] }
                : {
                    tipo_accion_id: tipoAccion_id,
                    form_ref_id: form_ref_id,
                    condiciones: []
                };

            if (tipoAccion_id === 'TAC-001') {
                const tipoAccion = document.getElementById('modal-tipo-accion');
                const formRef = document.getElementById('modal-form-ref');
                const campoRef = document.getElementById('modal-campo-ref');
                const operacion = document.getElementById('modal-operacion');
                const tipoValor = document.getElementById('modal-tipo-valor').value;
                const valor = tipoValor === 'static' ? document.getElementById('modal-valor-estatico').value : document.getElementById('modal-valor-campo').value;

                let valor_text;

                if (tipoValor === 'static') {
                    // Para valores estáticos (input o textarea)
                    valor_text = document.getElementById('modal-valor-estatico').value.trim();
                } else {
                    // Para selector de campo
                    const select = document.getElementById('modal-valor-campo');
                    valor_text = select.options[select.selectedIndex]?.text || '';
                }


                accionObj.form_ref_id = formRef.value;
                accionObj.campo_ref_id = campoRef.value;
                accionObj.operacion = operacion.value;
                accionObj.tipo_valor = tipoValor;
                accionObj.valor = valor;
                accionObj.valor_text = valor_text;


                accionObj.tipo_accion_text = tipoAccion.options[tipoAccion.selectedIndex]?.text || '';
                // Textos legibles
                accionObj.form_ref_text = formRef.options[formRef.selectedIndex]?.text || '';
                accionObj.campo_ref_text = campoRef.options[campoRef.selectedIndex]?.text || '';
                accionObj.operacion_text = operacion.options[operacion.selectedIndex]?.text || '';



            }


            if (tipoAccion_id === 'TAC-005') {
                const usarRelacion = document.getElementById('check-usar-relacion')?.checked;
                const radioSeleccionado = document.querySelector('#formularios-relacionados input[name="listGroupRadio"]:checked');

                accionObj.usar_relacion = usarRelacion;
                accionObj.formulario_relacion_seleccionado = radioSeleccionado ? radioSeleccionado.value : null;
                accionObj.formulario_relacion_text = radioSeleccionado
                    ? document.querySelector(`label[for="${radioSeleccionado.id}"]`)?.textContent.trim()
                    : null;



                accionObj.campos = [];




                const filas = document.querySelectorAll('#contenedor-campos-form .row[data-form-ref-id]');
                filas.forEach(fila => {
                    // Solo tomar filas que pertenecen al formulario de destino
                    const esFilaDestino = !radioSeleccionado || fila.dataset.formRefId !== radioSeleccionado.value;

                    if (!esFilaDestino) return; // Ignorar filas que solo muestran relación

                    const check = fila.querySelector('.usar-origen');
                    const selectOrigen = fila.querySelector('.campo-origen');
                    const inputDestino = fila.querySelector('.campo-destino');

                    const valorDestino = !check?.checked ? inputDestino.value : null;
                    const campoOrigenTexto = selectOrigen.options[selectOrigen.selectedIndex]?.text || '';
                    accionObj.campos.push({
                        campo_id: fila.dataset.campoId,
                        campo_nombre: fila.dataset.campoNombre,
                        usar_origen: check?.checked || false,
                        campo_origen_id: check?.checked ? selectOrigen.value : null,
                        campo_origen_text: campoOrigenTexto,
                        valor_destino: valorDestino
                    });

                    // Guardar filtros
                    accionObj.filtros_relacion = Object.values(filtrosGuardados);
                });

                accionObj.tipo_accion_text = document.getElementById('modal-tipo-accion').options[document.getElementById('modal-tipo-accion').selectedIndex]?.text || '';
            }

            // ===== TAC-003 / enviar_email =====
            if (tipoAccion_id === 'TAC-003' || tipoAccion_id === 'enviar_email') {
                // Asunto, mensaje y plantilla
                accionObj.email_subject = document.getElementById('modal-email-subject').value.trim();
                accionObj.email_body = editor.getData().trim();
                accionObj.email_template = document.getElementById('email-template')?.value || null;

                // Usuarios seleccionados (IDs)
                const usuariosSeleccionados = document.getElementById('usuarios-hidden').value
                    .split(',')
                    .filter(u => u);


                const existentes = accionObj.email_usuarios || [];

                accionObj.email_usuarios = [
                    ...new Set([
                        ...existentes,
                        ...usuariosSeleccionados
                    ])
                ];

                // Textos de usuarios seleccionados
                const usuariosTextos = Array.from(document.querySelectorAll('#user-list li'))
                    .filter(li => usuariosSeleccionados.includes(li.dataset.id))
                    .map(li => li.querySelector('span').textContent.trim());

                // Roles seleccionados (IDs) y textos
                const rolesInputs = Array.from(document.querySelectorAll('#modal-email-block input[name="roles[]"]:checked'));
                const rolesSeleccionados = rolesInputs.map(input => input.value);
                const rolesTextos = rolesInputs.map(input => {
                    const label = document.querySelector(`label[for="${input.id}"]`);
                    return label ? label.textContent.trim() : '';
                });

                accionObj.email_roles = rolesSeleccionados;

                // Campos seleccionados en el email
                const camposSeleccionados = [];
                document.querySelectorAll('#email-campos-origen button.selected').forEach(btn => {
                    camposSeleccionados.push({
                        tipo: 'origen',
                        nombre: btn.textContent,
                        valorPlantilla: `[${btn.dataset.nombreCampo || btn.textContent}]`
                    });
                });
                document.querySelectorAll('#email-campos-usuarios button.selected').forEach(btn => {
                    camposSeleccionados.push({
                        tipo: 'usuario',
                        nombre: btn.textContent,
                        valorPlantilla: `[${btn.dataset.nombreCampo}]`
                    });
                });
                accionObj.tipo_accion_text = document.getElementById('modal-tipo-accion').options[document.getElementById('modal-tipo-accion').selectedIndex]?.text || '';

                accionObj.email_detalle = {
                    to: accionObj.email_usuarios,
                    to_text: usuariosTextos,
                    roles: rolesSeleccionados,
                    roles_text: rolesTextos,
                    subject: accionObj.email_subject,
                    body: accionObj.email_body,
                    plantilla: accionObj.email_template,
                    camposUsados: camposSeleccionados
                };
            }

            // Condiciones
            document.querySelectorAll('#condiciones-modal-container .condicion-block').forEach(cond => {
                const origen = cond.querySelector('.cond-form-origen');
                const operador = cond.querySelector('.cond-operador');
                const destino = cond.querySelector('.cond-form-destino');

                accionObj.condiciones.push({
                    campo_condicion_origen: origen.value,
                    operador: operador.value,
                    campo_condicion_destino: destino.value,
                    campo_condicion_origen_text: origen.options[origen.selectedIndex]?.text || '',
                    operador_text: operador.options[operador.selectedIndex]?.text || '',
                    campo_condicion_destino_text: destino.options[destino.selectedIndex]?.text || ''
                });

            });

            return accionObj;
        }


        // Guardar acción desde el modal con validación
        document.getElementById('guardar-accion-modal').addEventListener('click', async () => {
            const tipoAccion = document.getElementById('modal-tipo-accion').value;
            if (!tipoAccion) {
                alertify.warning('Seleccione un tipo de acción');
                return;
            }


            const accionObj = capturarDatosModal();
            accionObj.filtros_relacion = Object.values(filtrosGuardados);

            if (tipoAccion === 'TAC-001') {

                const operacion = document.getElementById('modal-operacion').value;
                if (operacion == -1) {
                    alertify.warning('Seleccione un tipo de operación');
                    return;
                }

                if (!accionObj.form_ref_id || !accionObj.campo_ref_id || !accionObj.operacion || !accionObj.valor) {
                    alertify.warning('Complete todos los campos obligatorios para la acción "Modificar Campo".');
                    return;
                }
            }

            if (tipoAccion === 'TAC-005') {
                const usarRelacion = document.getElementById('check-usar-relacion')?.checked;
                const radioSeleccionado = document.querySelector('#formularios-relacionados input[name="listGroupRadio"]:checked');

                if (usarRelacion && !radioSeleccionado) {
                    alertify.warning('Seleccione un formulario relacionado.');
                    return;
                }

                const filas = document.querySelectorAll('#contenedor-campos-form .row[data-form-ref-id]');
                for (let fila of filas) {
                    const check = fila.querySelector('.usar-origen');
                    const selectOrigen = fila.querySelector('.campo-origen');
                    const inputDestino = fila.querySelector('.campo-destino');

                    // Obtener id del formulario de destino de la fila
                    const formRefId = fila.dataset.formRefId;

                    // Ignorar validación de input de destino si pertenece al formulario seleccionado por el radio
                    const estaBloqueadoPorRadio = usarRelacion && radioSeleccionado && formRefId === radioSeleccionado.value;

                    if (check?.checked && !selectOrigen.value) {
                        alertify.warning('Seleccione un campo de origen para todas las filas marcadas.');
                        return;
                    }

                    if (!check?.checked && !estaBloqueadoPorRadio && inputDestino?.hasAttribute('required') && !inputDestino.value) {
                        alertify.warning('Complete todos los campos obligatorios de destino.');
                        return;
                    }
                }
            }

            // ===== TAC-003 / enviar_email =====
            if (tipoAccion === 'TAC-003' || tipoAccion === 'enviar_email') {


                const plantilla = document.getElementById('email-template').value;
                // 1️⃣ Usuarios seleccionados

                const usuariosHidden = document.getElementById('usuarios-hidden').value;
                const usuarios = usuariosHidden ? usuariosHidden.split(',') : [];

                // 2️⃣ Roles seleccionados
                const roles = Array.from(
                    document.querySelectorAll('input[name="roles[]"]:checked')
                ).map(r => r.value);

                // 3️⃣ Asunto y mensaje
                const subject = document.getElementById('modal-email-subject').value.trim();
                const body = editor.getData().trim();

                /* ================= VALIDACIONES ================= */

                // Usuarios o roles (al menos uno)
                if (usuarios.length === 0 && roles.length === 0) {
                    alertify.warning('Seleccione al menos un usuario o un rol.');
                    return;
                }

                // Asunto obligatorio
                if (!subject) {
                    alertify.warning('El asunto del correo es obligatorio.');
                    document.getElementById('modal-email-subject').focus();
                    return;
                }

                // Mensaje obligatorio
                if (!body) {
                    alertify.warning('El mensaje del correo es obligatorio.');
                    document.getElementById('modal-email-body').focus();
                    return;
                }


                // Plantilla obligatoria
                if (!plantilla) {
                    alertify.warning('Debe seleccionar una plantilla.');
                    document.getElementById('email-template').focus();
                    return false;
                }

            }

            let condicionesInvalidas = accionObj.condiciones.some(c => !c.campo_condicion_origen || !c.operador || !c.campo_condicion_destino);
            if (condicionesInvalidas) {
                alertify.warning('Complete todos los campos de las condiciones.');
                return;
            }

            if (editingIndex !== null) {
                accionesArray[editingIndex] = accionObj;
                accionesJSONInput.value = JSON.stringify(accionesArray);
                console.log(accionesArray)
                console.log(accionObj)

                crearCardVisual(accionObj, editingIndex);
                editingIndex = null;
            } else {

                accionesArray.push(accionObj);
                accionesJSONInput.value = JSON.stringify(accionesArray);



                crearCardVisual(accionObj, accionesArray.length - 1);
            }

            modal.hide();


        });


        // Crear card visual
        function crearCardVisual(accionObj, index) {
            let cardWrapper;
            if (editingIndex !== null) {
                // Si estamos editando, reemplazamos el card existente
                cardWrapper = accionesList.children[editingIndex];
                cardWrapper.innerHTML = '';
            } else {
                // Crear columna contenedora para grid
                cardWrapper = document.createElement('div');
                cardWrapper.classList.add('col-md-12', 'mb-3');
                cardWrapper.dataset.index = index;

                // Append al row principal
                accionesList.appendChild(cardWrapper);
            }

            // Crear la card interna
            const card = document.createElement('div');
            card.classList.add('card', 'p-3', 'h-100', 'shadow-sm'); // padding y altura completa
            card.dataset.index = index;
            cardWrapper.appendChild(card);

            // Contenido de la card
            let cardHTML = `
                <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold small">
                    #${index + 1} - ${accionObj.tipo_accion_text}
                </span>

                    <div class="d-flex ">
                        <button 
                            type="button" 
                            class="btn btn-xs btn-outline-secondary view-accion-card" 
                            data-index="${index}"
                            title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button 
                            type="button" 
                            class="btn btn-xs btn-outline-primary edit-accion-card" 
                            data-index="${index}"
                            title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button 
                            type="button" 
                            class="btn btn-xs btn-outline-danger remove-accion-card" 
                            data-index="${index}"
                            title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            card.innerHTML = cardHTML;
            // EDITAR
            card.querySelector('.edit-accion-card')?.addEventListener('click', async (e) => {

                // arreglo global o contexto donde guardas las acciones
                await abrirModalEdicion(accionObj, index);
            });

            // ELIMINAR
            card.querySelector('.remove-accion-card')?.addEventListener('click', (e) => {
                const index = parseInt(e.currentTarget.dataset.index);
                eliminarAccion(index); // o la lógica que ya tengas
            });

            card.querySelector('.view-accion-card')?.addEventListener('click', () => {

                const contenido = generarContenidoAccion(accionObj, index);

                document.querySelector('#modalVerAccion .modal-body').innerHTML = contenido;

                const modal = new bootstrap.Modal(document.getElementById('modalVerAccion'));
                modal.show();
            });

            // Reasignar índices de las columnas
            Array.from(accionesList.children).forEach((c, i) => c.dataset.index = i);
        }



        function generarContenidoAccion(accionObj, index) {

            let contenido = `<h6><strong>Acción #${index + 1} - ${accionObj.tipo_accion_text}</strong></h6><hr>`;

            if (accionObj.tipo_accion_id === 'TAC-001') {
                contenido += `
        <p>
        <strong>Formulario destino:</strong> ${accionObj.form_ref_text}<br>
        <strong>Campo:</strong> ${accionObj.campo_ref_text}<br>
        <strong>Operación:</strong> ${accionObj.operacion_text}<br>
        <strong>Valor:</strong> ${accionObj.valor_text}
        </p>`;
            }

            if (accionObj.tipo_accion_id === 'TAC-005') {
                contenido += `<p><strong>Usar relación:</strong> ${accionObj.usar_relacion ? 'Sí' : 'No'}<br>`;

                if (accionObj.usar_relacion && accionObj.formulario_relacion_seleccionado) {
                    contenido += `<strong>Formulario relacionado:</strong> ${accionObj.formulario_relacion_text}<br>`;
                }

                if (accionObj.campos?.length) {
                    contenido += `<strong>Campos:</strong><br>`;
                    accionObj.campos.forEach(c => {
                        const origen = c.usar_origen ? `Usa origen: ${c.campo_origen_text}` : '';
                        const destino = !c.usar_origen && c.valor_destino ? `Valor destino: ${c.valor_destino}` : '';
                        contenido += `- <strong>${c.campo_nombre}</strong> ${origen} ${destino}<br>`;
                    });
                }

                contenido += `</p>`;
            }

            if (accionObj.tipo_accion_id === 'TAC-003') {

                const usuariosText = (accionObj.email_detalle?.to_text || []).join(', ') || 'Ninguno';
                const rolesText = (accionObj.email_detalle?.roles_text || []).join(', ') || 'Ninguno';

                contenido += `
        <p>
        <strong>Usuarios:</strong> ${usuariosText}<br>
        <strong>Roles:</strong> ${rolesText}<br>
        <strong>Asunto:</strong> ${accionObj.email_subject || ''}<br>
        <strong>Mensaje:</strong><br>
        <div class="border rounded p-2 bg-light">
            ${accionObj.email_body || ''}
        </div>
        </p>`;
            }

            if (accionObj.condiciones?.length) {
                contenido += `<hr><strong>Condiciones:</strong><br>`;
                accionObj.condiciones.forEach(c => {
                    contenido += `${c.campo_condicion_origen_text} ${c.operador_text || c.operador} ${c.campo_condicion_destino_text}<br>`;
                });
            }

            return contenido;
        }








        // listener para modal-tipo-valor
        /*PARA TAC-001*/
        document.getElementById('modal-tipo-valor').addEventListener('change', e => {
            const tipo = e.target.value;
            const inputEstatico = document.getElementById('modal-valor-estatico');
            const selectCampo = document.getElementById('modal-valor-campo');
            const formId = document.getElementById('formulario_id').value;

            if (tipo === 'campo') {
                inputEstatico.classList.add('d-none');
                selectCampo.classList.remove('d-none');
                cargarCamposCached(formId, selectCampo, '-- Seleccione campo origen --');
            } else {
                inputEstatico.classList.remove('d-none');
                selectCampo.classList.add('d-none');
            }
        });




        // Abrir modal edición con autocompletado usando cache


        async function abrirModalEdicion(accion, index) {

            document.getElementById('guardar-accion-modal').textContent = 'Guardar cambios';

            limpiarModal();
            editingIndex = index;

            const tipoAccionSelect = document.getElementById('modal-tipo-accion');
            tipoAccionSelect.value = accion.tipo_accion_id;
            tipoAccionSelect.dispatchEvent(new Event('change'));

            if (accion.tipo_accion_id === 'TAC-001') {
                const formSelect = document.getElementById('modal-form-ref');
                const campoSelect = document.getElementById('modal-campo-ref');
                const operacionSelect = document.getElementById('modal-operacion');

                // IDs correctos: formulario origen (el form principal) y formulario destino (formRef)
                const formOrigenId = document.querySelector('.select-formulario').value; // <-- ORIGEN
                const formDestinoId = accion.form_ref_id || formSelect.value; // <-- DESTINO (para campo_ref)

                // Setear el form destino (para elegir campo_destino)
                formSelect.value = accion.form_ref_id;
                formSelect.dispatchEvent(new Event('change'));

                // Cargar campos del formulario destino para campo_ref (correcto)
                await cargarCamposCached(formDestinoId, campoSelect, '-- Seleccione campo destino --');
                campoSelect.value = accion.campo_ref_id;

                // Operación
                operacionSelect.value = accion.operacion;

                // Tipo de valor (static / campo) y cargar select correspondiente
                document.getElementById('modal-tipo-valor').value = accion.tipo_valor;
                document.getElementById('modal-tipo-valor').dispatchEvent(new Event('change'));

                if (accion.tipo_valor === 'static') {
                    document.getElementById('modal-valor-estatico').value = accion.valor;
                } else {
                    // IMPORTANTÍSIMO: cargar campos del FORMULARIO ORIGEN para el select "modal-valor-campo"
                    const valorCampoSelect = document.getElementById('modal-valor-campo');

                    // <-- aquí estaba el error: no uses form_ref_id, usa formOrigenId
                    await cargarCamposCached(formOrigenId, valorCampoSelect, '-- Seleccione campo origen --');

                    // luego asigna el valor (id del campo origen)
                    valorCampoSelect.value = accion.valor;
                }
            }




            if (accion.tipo_accion_id === 'TAC-003') {
                console.log(accion)
                /* ================================
            ASUNTO
            ================================= */
                document.getElementById('modal-email-subject').value = accion.email_subject || '';

                /* ================================
                   MENSAJE (CKEditor)
                ================================= */
                if (typeof editor !== 'undefined') {
                    editor.setData(accion.email_body || '');
                } else {
                    document.getElementById('modal-email-body').value = accion.email_body || '';
                }

                /* ================================
                   PLANTILLA
                ================================= */
                document.getElementById('email-template').value = accion.email_template || '';

                /* ================================
                   USUARIOS
                ================================= */
                const userList = document.getElementById('user-list');
                const hiddenInput = document.getElementById('usuarios-hidden');

                userList.innerHTML = '';
                usuariosSeleccionados = [];

                if (accion.email_usuarios && accion.email_usuarios.length) {

                    accion.email_usuarios.forEach(userId => {

                        const select = document.getElementById('user-selector');
                        const option = select.querySelector(`option[value="${userId}"]`);

                        if (!option) return;

                        const userText = option.text;

                        usuariosSeleccionados.push(String(userId));

                        const li = document.createElement('li');
                        li.dataset.id = userId;
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';

                        const span = document.createElement('span');
                        span.textContent = userText;

                        const button = document.createElement('button');
                        button.className = 'btn btn-xs btn-danger';
                        button.textContent = 'X';

                        li.appendChild(span);
                        li.appendChild(button);

                        li.querySelector('button').onclick = () => {
                            alertify.confirm(
                                'Confirmación',
                                '¿Está seguro de quitar este usuario de la lista?',
                                function () {
                                    usuariosSeleccionados = usuariosSeleccionados.filter(id => id !== String(userId));
                                    userList.removeChild(li);
                                    actualizarHiddenUsuarios();
                                    alertify.success('Usuario eliminado');
                                },
                                function () {
                                    alertify.message('Acción cancelada');
                                }
                            );
                        };

                        userList.appendChild(li);
                    });
                }

                actualizarHiddenUsuarios();

                /* ================================
                   ROLES
                ================================= */
                document.querySelectorAll('input[name="roles[]"]').forEach(check => {
                    check.checked = false;
                });

                if (accion.email_roles && accion.email_roles.length) {
                    accion.email_roles.forEach(roleId => {
                        const checkbox = document.getElementById(`rol_${roleId}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            }

            // Condiciones
            const container = document.getElementById('condiciones-modal-container');
            container.innerHTML = '';
            for (const c of accion.condiciones) {
                agregarCondicionModal({
                    campo_origen_id: c.campo_condicion_origen,
                    operador: c.operador,
                    campo_destino_id: c.campo_condicion_destino
                });
            }




        }

        function actualizarHiddenUsuarios() {
            document.getElementById('usuarios-hidden').value = usuariosSeleccionados.join(',');
        }

        async function obtenerCamposUsuario() {
            const res = await fetch('/email/campos-usuario');
            const json = await res.json();
            return json.data || [];
        }

        /*PARA TAC-001*/

        // abrir modal para agregar acción
        document.getElementById('open-modal-accion').addEventListener('click', async () => {
            const nombreRegla = document.querySelector('input[name="nombre"]').value.trim();
            const formularioOrigen = formularioPrincipal.value;
            const evento = document.querySelector('select[name="evento"]').value;
            if (!nombreRegla) { alertify.error('Ingrese el nombre de la regla'); return; }
            if (!formularioOrigen) { alertify.error('Seleccione el formulario de origen'); return; }
            if (!evento) { alertify.error('Seleccione el evento'); return; }


            const tipoAccionSelect = document.getElementById('modal-tipo-accion');

            if (tipoAccionSelect.value == "") {
                alertify.warning('Seleccione un tipo de Acción');
                return
            }


            limpiarModal();
            await inicializarModalOptimizado();

        });

        document.getElementById('modal-form-ref').addEventListener('change', async () => {
            await inicializarModalOptimizado();
        });



        /*TAC-005*/
        function getInfoForm(editData = null) {
            const formDestinoId = editData?.formulario_destino_id || document.getElementById('modal-form-ref_crear_registros').value;



            fetch(`/form-destino/info/${formDestinoId}`)
                .then(res => res.json())
                .then(data => {
                    const contenedor = document.getElementById('formularios-relacionados');
                    contenedor.innerHTML = ''; // limpiar contenido previo

                    if (data.formulariosRelacionados.length > 0) {
                        const row = document.createElement('div');
                        row.className = 'row';

                        // Columna radios
                        const colRadios = document.createElement('div');
                        colRadios.className = 'col-6';

                        const ul = document.createElement('ul');
                        ul.className = 'list-group';

                        data.formulariosRelacionados.forEach((form, index) => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';

                            const formCheckDiv = document.createElement('div');
                            formCheckDiv.className = 'form-check';

                            const input = document.createElement('input');
                            input.type = 'radio';
                            input.name = 'listGroupRadio';
                            input.className = 'form-check-input';
                            input.id = `radio-${form.id}`;
                            input.value = form.id;
                            input.checked = false;

                            const label = document.createElement('label');
                            label.className = 'form-check-label fw-bold ms-2';
                            label.htmlFor = input.id;
                            label.textContent = form.nombre;

                            formCheckDiv.appendChild(input);
                            formCheckDiv.appendChild(label);
                            li.appendChild(formCheckDiv);

                            // Botones informativos para campos
                            if (form.campos.length > 0) {
                                const camposContainer = document.createElement('div');
                                camposContainer.className = 'mt-2';

                                form.campos.forEach(campo => {
                                    const boton = document.createElement('button');
                                    boton.type = 'button';
                                    boton.className = 'btn btn-xs btn-outline-secondary me-1 mb-1';
                                    boton.textContent = campo.nombre;
                                    boton.disabled = true;
                                    camposContainer.appendChild(boton);
                                });

                                li.appendChild(camposContainer);
                            }

                            ul.appendChild(li);

                            // Evento al seleccionar radio
                            input.addEventListener('change', () => {

                                activarBloqueoCampos(input.value);

                                const colCard = document.getElementById('form-relacion-card');
                                colCard.innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Formulario: ${form.nombre}</h5>
                                    <div id="filtros-container" class="mb-3"></div>
                                    <div id="campos-generados" class="row g-3 mb-3"></div>
                                    <button class="btn btn-secondary" id="btn-filtrar-relacion">Filtrar</button>
                                </div>
                            </div>
                        `;


                                const btnFiltrar = colCard.querySelector('#btn-filtrar-relacion');
                                btnFiltrar.addEventListener('click', async () => {
                                    const modalFiltrar = new bootstrap.Modal(document.getElementById('modalFiltrar'));

                                    // Limpiar selects del modal
                                    const selectCampoRelacion = document.getElementById('select-campo-relacion');
                                    selectCampoRelacion.innerHTML = '';
                                    form.campos.forEach(c => {
                                        const option = document.createElement('option');
                                        option.value = c.id;
                                        option.textContent = c.nombre;
                                        selectCampoRelacion.appendChild(option);
                                    });

                                    const selectFormularioOrigen = document.querySelector('.select-formulario');
                                    const selectCampoOrigen = document.getElementById('select-campo-origen-filtro');
                                    const formOrigenId = selectFormularioOrigen.value;

                                    await cargarCamposConCache(formOrigenId, selectCampoOrigen, 'origen', '-- Seleccione campo origen --');

                                    // Guardar filtro
                                    document.getElementById('btn-guardar-filtro').onclick = () => {
                                        const campoRelacion = selectCampoRelacion.value;
                                        const campoRelacionText = selectCampoRelacion.options[selectCampoRelacion.selectedIndex]?.text || '';
                                        const condicion = document.getElementById('select-condicion').value;
                                        const campoOrigen = selectCampoOrigen.value;
                                        const campoOrigenText = selectCampoOrigen.options[selectCampoOrigen.selectedIndex]?.text || '';

                                        if (!campoRelacion || !condicion || !campoOrigen) {
                                            alertify.warning('Complete todos los campos del filtro.');
                                            return;
                                        }

                                        const filtrosContainer = colCard.querySelector('#filtros-container');

                                        // Generar un id único para el filtro
                                        const filtroId = `${campoRelacion}_${condicion}_${campoOrigen}`;

                                        const badge = document.createElement('span');
                                        badge.className = 'badge bg-info text-dark me-1 mb-1';
                                        badge.innerHTML = `${campoRelacionText} ${condicion} ${campoOrigenText} 
                                        <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Cerrar"></button>`;

                                        const btnCerrar = badge.querySelector('button');
                                        btnCerrar.addEventListener('click', () => {
                                            alertify.confirm(
                                                'Eliminar filtro',
                                                '¿Está seguro de que desea eliminar este filtro?',
                                                function () {
                                                    delete filtrosGuardados[filtroId]; // eliminar del objeto
                                                    badge.remove();
                                                    alertify.success('Filtro eliminado');
                                                },
                                                function () {
                                                    alertify.message('Acción cancelada');
                                                }
                                            ).set('labels', { ok: 'Sí', cancel: 'Cancelar' });
                                        });

                                        filtrosContainer.appendChild(badge);

                                        // Guardar en el objeto
                                        filtrosGuardados[filtroId] = {
                                            campoRelacion,
                                            campoRelacionText,
                                            condicion,
                                            campoOrigen,
                                            campoOrigenText
                                        };

                                        modalFiltrar.hide();
                                    };

                                    modalFiltrar.show();
                                });
                            });
                        });

                        colRadios.appendChild(ul);
                        row.appendChild(colRadios);

                        const colCard = document.createElement('div');
                        colCard.className = 'col-6';
                        colCard.id = 'form-relacion-card';
                        row.appendChild(colCard);

                        contenedor.appendChild(row);



                        const check = document.getElementById('check-usar-relacion');
                        check.replaceWith(check.cloneNode(true));
                        const nuevoCheck = document.getElementById('check-usar-relacion');


                        nuevoCheck.addEventListener('change', () => {
                            const radiosContainer = document.getElementById('formularios-relacionados');
                            const inputCantidad = document.getElementById('input-cantidad');

                            if (nuevoCheck.checked) {
                                radiosContainer.style.display = 'block';
                                inputCantidad.disabled = true;

                                // Activar bloqueo cuando se seleccione algún radio
                                document.querySelectorAll('input[name="listGroupRadio"]').forEach(radio => {
                                    radio.addEventListener('change', () => {
                                        activarBloqueoCampos(radio.value);
                                    });
                                });

                            } else {
                                radiosContainer.style.display = 'none';
                                inputCantidad.disabled = false;
                                getInfoForm();
                                // Desbloquear todas las filas
                                document.querySelectorAll('#contenedor-campos-form .row[data-form-ref-id]').forEach(fila => {
                                    fila.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
                                });
                            }
                        });




                    } else {
                        contenedor.innerHTML = '<p>No hay formularios relacionados</p>';
                    }

                })
                .catch(err => console.error(err));
        }



        async function construirCamposFormulario(formulario, contenedorDestino, formOrigenId) {
            if (!formulario || !formulario.campos) return;

            contenedorDestino.innerHTML = ''; // Limpiar contenido previo

            // Encabezado
            const encabezado = document.createElement('div');
            encabezado.className = 'row align-items-center mb-1 text-center border-bottom pb-1';
            encabezado.innerHTML = `
        <div class="col-md-5 text-start small fw-bold">Campos del Formulario de Destino</div>
        <div class="col-md-2 text-center">
            <i class="fas fa-info-circle small text-white" data-bs-toggle="tooltip" title="Marque si desea usar un campo del formulario de origen" style="cursor: pointer;"></i>
        </div>
        <div class="col-md-5 text-end small fw-bold">Campos del Formulario de Origen</div>
    `;
            // Inicializar tooltip
            const tooltipTriggerList = encabezado.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
            contenedorDestino.appendChild(encabezado);

            formulario.campos.forEach(campo => {
                const row = document.createElement('div');
                row.className = 'row align-items-center mb-3';
                row.id = `fila_campo_${campo.id}`; // <-- ID único para la fila
                row.dataset.formRefId = campo.form_ref_id;
                row.dataset.campoId = campo.id;
                row.dataset.campoNombre = campo.etiqueta;
                // Columna campo destino
                const colDestino = document.createElement('div');
                colDestino.className = 'col-md-5';
                let htmlCampo = `<label class="form-label fw-bold mb-1">${campo.etiqueta || campo.nombre}${campo.requerido ? '<span class="text-danger">*</span>' : ''}</label>`;
                htmlCampo += `<input type="text" name="${campo.nombre}" class="form-control campo-destino" ${campo.requerido ? 'required' : ''}>`;
                colDestino.innerHTML = htmlCampo;

                // Columna check
                const colCheck = document.createElement('div');
                colCheck.className = 'col-md-2 text-center';

                colCheck.innerHTML = `
                    <div class="form-check d-inline-block">
                        <input type="checkbox" class="form-check-input usar-origen" id="check_${campo.id}" title="Usar campo del formulario de origen">
                        <label class="form-check-label" for="check_${campo.id}"></label>
                    </div>
                `;
                // Columna selector origen
                const colOrigen = document.createElement('div');
                colOrigen.className = 'col-md-5';
                colOrigen.innerHTML = `<select id="select_origen_${campo.id}" class="form-select campo-origen" disabled>
            <option value="">-- Seleccione campo origen --</option>
        </select>`;

                const check = colCheck.querySelector('.usar-origen');
                const campoDestino = colDestino.querySelector('.campo-destino');
                const selectOrigen = colOrigen.querySelector('.campo-origen');

                // Evento checkbox
                check.addEventListener('change', async () => {
                    if (check.checked) {
                        campoDestino.disabled = true;
                        selectOrigen.disabled = false;
                        await cargarCamposConCache(formOrigenId, selectOrigen, 'origen', '-- Seleccione campo origen --');
                    } else {
                        campoDestino.disabled = false;
                        selectOrigen.disabled = true;
                        selectOrigen.innerHTML = `<option value="">-- Seleccione campo origen --</option>`;
                    }

                    // Bloquear/desbloquear fila si hay radio seleccionado
                    const radioSeleccionado = document.querySelector('#formularios-relacionados input[name="listGroupRadio"]:checked');
                    if (!check.checked || !radioSeleccionado) {
                        row.querySelectorAll('input, select').forEach(el => el.disabled = false);
                    }
                });

                row.appendChild(colDestino);
                row.appendChild(colCheck);
                row.appendChild(colOrigen);
                contenedorDestino.appendChild(row);
            });
        }


        // Evento de radio del formulario relacionado
        function activarBloqueoCampos(formRefIdSeleccionado) {
            if (!formRefIdSeleccionado) return;

            // recorrer todas las filas que tengan el data-form-ref-id
            document.querySelectorAll('#contenedor-campos-form .row[data-form-ref-id]').forEach(fila => {
                if (fila.dataset.formRefId === formRefIdSeleccionado) {
                    // bloquear todas las columnas de esa fila
                    fila.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
                } else {
                    // desbloquear las demás filas
                    fila.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
                }
            });
        }



        document.getElementById('modal-form-ref_crear_registros').addEventListener('change', async () => {
            const formDestinoId = document.getElementById('modal-form-ref_crear_registros').value;
            const opcionesContainer = document.getElementById('opciones-relacion');
            const radiosContainer = document.getElementById('formularios-relacionados');
            const check = document.getElementById('check-usar-relacion');
            const inputCantidad = document.getElementById('input-cantidad');

            // Limpiar radios previos
            radiosContainer.innerHTML = '';

            if (!formDestinoId) {
                opcionesContainer.style.display = 'none';
                radiosContainer.style.display = 'none';
                check.checked = false;
                inputCantidad.disabled = false;
                return;
            }

            const res = await fetch(`/form-destino/info/${formDestinoId}`);
            const data = await res.json();

            if (data.formulariosRelacionados && data.formulariosRelacionados.length > 0) {
                opcionesContainer.style.display = 'block';
                check.checked = false;

                radiosContainer.style.display = 'none';
                inputCantidad.disabled = false;

                // Eliminar posibles eventos duplicados en el check
                check.replaceWith(check.cloneNode(true));
                const nuevoCheck = document.getElementById('check-usar-relacion');

                nuevoCheck.addEventListener('change', () => {
                    // Limpiar radios previos antes de recargar info
                    const radiosContainer = document.getElementById('formularios-relacionados');
                    radiosContainer.innerHTML = '';

                    if (nuevoCheck.checked) {
                        radiosContainer.style.display = 'block';
                        inputCantidad.disabled = true;
                        getInfoForm();


                    } else {


                        inputCantidad.disabled = false;
                        radiosContainer.style.display = 'none';
                    }
                });

            } else {
                opcionesContainer.style.display = 'none';
                radiosContainer.style.display = 'none';
                inputCantidad.disabled = false;
            }

            // LLAMADA A construirCamposFormulario
            const contenedorCampos = document.getElementById('contenedor-campos-form');
            if (contenedorCampos) {
                contenedorCampos.innerHTML = ''; // limpiar antes

                const formularioDestino = {
                    campos: data.campos || data.formulario?.campos || []
                };

                // Obtener ID del formulario de origen
                const formOrigenSelect = document.querySelector('select[name="formulario_id"]');
                const formOrigenId = formOrigenSelect ? formOrigenSelect.value : null;

                // Llamada a la función que construye los campos con soporte para checkbox + selector de origen
                construirCamposFormulario(formularioDestino, contenedorCampos, formOrigenId);
            }
        });

        /*TAC-005*/



        /*  TAC-003*/

        async function cargarCamposOrigenParaEmail() {
            const formOrigenId = formularioPrincipal.value;
            const contenedor = document.getElementById('email-campos-origen');
            const textarea = document.getElementById('modal-email-body');

            if (!formOrigenId || !contenedor) return;

            contenedor.innerHTML = '<span class="text-muted small">Cargando campos...</span>';
            const contenedor_usuarios = document.getElementById('email-campos-usuarios');


            contenedor_usuarios.innerHTML = '';
            var camposUsuario = await obtenerCamposUsuario();

            /* =========================
                CAMPOS DE USUARIO
             ========================= */
            camposUsuario.forEach(campo => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary btn-xs campo-btn';
                btn.textContent = campo.label;

                btn.addEventListener('click', () => {
                    insertarTextoEnEditor(editor, `[${campo.nombre}]`);
                });

                contenedor_usuarios.appendChild(btn);
            });




            // Reutiliza cache existente
            await cargarCamposCached(formOrigenId, document.createElement('select'), '--');

            const campos = camposCache[formOrigenId] || [];
            contenedor.innerHTML = '';



            if (!campos.length) {
                contenedor.innerHTML = '<span class="text-muted small">Sin campos disponibles</span>';
                return;
            }

            campos.forEach(campo => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary btn-xs campo-btn';
                btn.textContent = campo.nombre;

                btn.addEventListener('click', () => {
                    insertarTextoEnEditor(editor, `[${campo.nombre}]`);
                });

                contenedor.appendChild(btn);
            });
        }



        // Función para insertar texto en CKEditor
        function insertarTextoEnEditor(editorInstance, texto) {
            if (!editorInstance) return; // seguridad
            editorInstance.model.change(writer => {
                const selection = editorInstance.model.document.selection;
                const position = selection.getFirstPosition();
                writer.insertText(texto, position);
            });
            editorInstance.editing.view.focus();
        }

        /*  TAC-003*/
    });


</script>