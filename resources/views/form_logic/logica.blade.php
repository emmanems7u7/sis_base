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
    let editingIndex = null;

    const selectOperacion = document.getElementById("modal-operacion");
    const invertirCol = document.getElementById("col-invertir-operacion");

    function toggleInvertir() {
        if (selectOperacion.value === "OPC-012") {
            invertirCol.style.display = "block";
        } else {
            invertirCol.style.display = "none";
            document.getElementById("invertir_operacion").checked = false;
        }
    }

    selectOperacion.addEventListener("change", toggleInvertir);


    // Objeto para almacenar filtros
    const filtrosGuardados = {};

    const formularioDisparador = document.getElementById('formulario_id_disparador');
    const accionesList = document.getElementById('acciones-list');
    const accionesJSONInput = document.getElementById('acciones-json');
    const contenedor_botones = document.getElementById('contenedor_botones');
    const contenedor_condiciones = document.getElementById('contenedor_condiciones');
    const contenedor_mensaje = document.getElementById('contenedor_mensaje');
    const tipoAccionSelect = document.getElementById('modal-tipo-accion');


    let accionesArray = [];

    // 🔹 CACHE DE CAMPOS
    let lastFormOrigenId = null;
    let lastFormDestinoId = null;

</script>

<script src="{{ asset('js/FormLogic/CargaCampos.js') }}"></script>
<script src="{{ asset('js/FormLogic/Renders.js') }}"></script>
<script src="{{ asset('js/FormLogic/LogicaCondiciones.js') }}"></script>

<script src="{{ asset('js/FormLogic/TAC-001.js') }}"></script>
<script src="{{ asset('js/FormLogic/TAC-003.js') }}"></script>
<script src="{{ asset('js/FormLogic/TAC-005.js') }}"></script>
<script src="{{ asset('js/FormLogic/TAC-006.js') }}"></script>

<script>


    function restaurarInicio() {

        // Ocultar todos los bloques
        document.getElementById('modal-modificar-campo').classList.add('d-none');
        document.getElementById('modal-email-block').classList.add('d-none');

        // Mostrar según selección
        document.getElementById('modal-modificar-campo')?.classList.add('d-none');
        document.getElementById('modal-email-block')?.classList.add('d-none');
        document.getElementById('modal-crear-registros')?.classList.add('d-none');
        document.getElementById('cont-eliminar-registro')?.classList.add('d-none');

        document.getElementById('guardar-accion-logica').textContent = 'Agregar Acción';
        contenedor_botones.classList.add('d-none')
        contenedor_condiciones.classList.add('d-none')
        contenedor_mensaje.classList.remove('d-none')

    }




    function actualizarHiddenUsuarios() {
        document.getElementById('usuarios-hidden').value = usuariosSeleccionados.join(',');
    }

    //Cargar acciones iniciales
    if (accionesIniciales && accionesIniciales.length) {
        accionesArray = accionesIniciales;
        accionesArray.forEach((accion, index) => {
            crearCardVisual(accion, index);
        });
    }

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
            restaurarInicio()
            document.getElementById('modal-tipo-accion').value = '';
            LimpiarContenedor()
            mostrarAlerta('success', 'Acción creada correctamente.');
        });


    document.getElementById('modal-form-ref').addEventListener('change',
        async () => {
            await InicializarContenedorOptimizado();
        });

    async function InicializarContenedorOptimizado(editData = null) {



        const formOrigenId = formularioDisparador.value;
        const formDestinoId = editData?.formulario_destino_id || document.getElementById('modal-form-ref').value;

        const promesas = [];

        if (formOrigenId && formOrigenId !== lastFormOrigenId) {
            lastFormOrigenId = formOrigenId;
            promesas.push(cargarCamposConCache(formOrigenId, document.getElementById('modal-valor-campo'), 'origen', '-- Seleccione campo origen --'));


            document.querySelectorAll('#condiciones-container .cond-form-origen')
                .forEach(sel => promesas.push(cargarCamposConCache(formOrigenId, sel, 'origen', '-- Seleccione campo origen --')));
        }

        // campos destino
        if (formDestinoId && formDestinoId !== lastFormDestinoId) {
            lastFormDestinoId = formDestinoId;
            promesas.push(cargarCamposConCache(formDestinoId, document.getElementById('modal-campo-ref'), 'destino', '-- Seleccione campo destino --'));
            document.querySelectorAll('#condiciones-container .cond-form-destino')
                .forEach(sel => promesas.push(cargarCamposConCache(formDestinoId, sel, 'destino', '-- Seleccione campo destino --')));
        }

        await Promise.all(promesas);

        // si estamos editando, precargar valores

    }


    function LimpiarContenedor() {
        document.getElementById('modal-modificar-campo').classList.add('d-none');
        document.getElementById('modal-email-block').classList.add('d-none');
        document.getElementById('condiciones-container').innerHTML = '';
        document.getElementById('modal-form-ref').value = '';
        document.getElementById('modal-campo-ref').innerHTML = '<option value="">-- Ninguno --</option>';
        document.getElementById('modal-operacion').value = '-1';
        document.getElementById('tipo-valor').value = 'static';
        document.getElementById('modal-valor-estatico').value = '';
        document.getElementById('modal-valor-campo').innerHTML = '<option value="">-- Seleccionar campo --</option>';
        document.getElementById('modal-email-subject').value = '';

        editor.setData('');

    }

    //Función para capturar datos
    function CapturarDatos() {
        const tipoAccion_id = document.getElementById('modal-tipo-accion').value;

        var form_ref_id = document.getElementById('modal-form-ref_crear_registros').value;
        const accionObj = editingIndex !== null
            ? { ...accionesArray[editingIndex], condiciones: [] }
            : {
                tipo_accion_id: tipoAccion_id,
                form_ref_id: form_ref_id,
                condiciones: []
            };

        if (tipoAccion_id === 'TAC-001') {
            const tipoAccion = document.getElementById('modal-tipo-accion');
            const formRef = document.getElementById('modal-form-ref');
            const form_origen_id = document.getElementById('formulario_id');

            const campoRef = document.getElementById('modal-campo-ref');
            const operacion = document.getElementById('modal-operacion');
            const tipoValor = document.getElementById('tipo-valor').value;
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

            const checkbox = document.getElementById("invertir_operacion");

            const operacion_rev = checkbox.checked ? 1 : 0;

            accionObj.form_origen_id = form_origen_id.value;

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
            accionObj.operacion_rev = operacion_rev;


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



        if (tipoAccion_id === 'TAC-006') {

            const tipoAccion = document.getElementById('modal-tipo-accion');

            const form_origen_id = document.getElementById('delete-formulario-origen');

            const deleteFormDestino = document.getElementById('delete-formulario-destino');

            const condiciones = [];

            document.querySelectorAll('.delete-condicion-item')
                .forEach(item => {

                    const campoRef = item.querySelector('.delete-campo-ref');
                    const operacion = item.querySelector('.delete-operacion');

                    const tipoValor = item.querySelector('.delete-tipo-valor');

                    const valorEstatico = item.querySelector('.delete-valor-estatico');

                    const valorCampo = item.querySelector('.delete-valor-campo');

                    const mensaje = item.querySelector('.cond-mensaje');

                    const tipoValorValue = tipoValor.value;

                    const valor = tipoValorValue === 'static'
                        ? valorEstatico.value
                        : valorCampo.value;

                    let valor_text = '';

                    if (tipoValorValue === 'static') {

                        valor_text = valorEstatico.value.trim();

                    } else {

                        valor_text =
                            valorCampo.options[valorCampo.selectedIndex]?.text || '';

                    }

                    condiciones.push({


                        tipo_valor: tipoValorValue,

                        campo_condicion_origen: valor,
                        campo_condicion_origen_text: valor_text,

                        operador: operacion.value,
                        operador_text: operacion.options[operacion.selectedIndex]?.text || '',

                        campo_condicion_destino: campoRef.value,
                        campo_condicion_destino_text: campoRef.options[campoRef.selectedIndex]?.text || '',

                        mensaje: mensaje?.value || ''

                    });

                });

            accionObj.form_ref_id = deleteFormDestino.value;
            accionObj.form_origen_id = form_origen_id.value;
            accionObj.condiciones = condiciones;

            accionObj.tipo_accion_text =
                tipoAccion.options[tipoAccion.selectedIndex]?.text || '';

        }





        if (tipoAccion_id != 'TAC-006') {
            // Condiciones
            document.querySelectorAll('#condiciones-container .condicion-block').forEach(cond => {
                const origen = cond.querySelector('.cond-form-origen');
                const operador = cond.querySelector('.cond-operador');
                const destino = cond.querySelector('.cond-form-destino');
                const mensaje = cond.querySelector('.cond-mensaje');

                accionObj.condiciones.push({
                    campo_condicion_origen: origen.value,
                    operador: operador.value,
                    campo_condicion_destino: destino.value,
                    campo_condicion_origen_text: origen.options[origen.selectedIndex]?.text || '',
                    operador_text: operador.options[operador.selectedIndex]?.text || '',
                    campo_condicion_destino_text: destino.options[destino.selectedIndex]?.text || '',
                    mensaje: mensaje?.value || ''
                });

            });
        }
        document.querySelectorAll('#condiciones-container .condicion-form-valor-block')
            .forEach(cond => {

                const tipoFormulario = cond.querySelector('.cond-tipo-formulario');
                const campo = cond.querySelector('.cond-campo');
                const operador = cond.querySelector('.cond-operador');
                const valor = cond.querySelector('.cond-valor');

                accionObj.condiciones.push({

                    tipo_condicion: 'form_valor',

                    formulario_tipo: tipoFormulario.value,
                    formulario_tipo_text:
                        tipoFormulario.options[tipoFormulario.selectedIndex]?.text || '',

                    campo_condicion: campo.value,
                    campo_condicion_text:
                        campo.options[campo.selectedIndex]?.text || '',

                    operador: operador.value,
                    operador_text:
                        operador.options[operador.selectedIndex]?.text || '',

                    valor: valor.value
                });

            });

        return accionObj;
    }

    function CargaTipoAccion() {
        const tipo = tipoAccionSelect.value;

        restaurarInicio()

        contenedor_mensaje.classList.add('d-none')
        contenedor_condiciones.classList.remove('d-none')
        contenedor_botones.classList.remove('d-none')

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
            case 'TAC-006':
                document.getElementById('cont-eliminar-registro').classList.remove('d-none');
                contenedor_condiciones.classList.add('d-none')

                break;
        }

    }



    async function EjecutarEdicionTAC003(accion) {
        // ASUNTO
        document.getElementById('modal-email-subject').value = accion.email_subject || '';

        //MENSAJE (CKEditor)
        if (typeof editor !== 'undefined') {
            editor.setData(accion.email_body || '');
        } else {
            document.getElementById('modal-email-body').value = accion.email_body || '';
        }

        //  PLANTILLA
        document.getElementById('email-template').value = accion.email_template || '';

        //  USUARIOS
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


                    mostrarAlerta('confirm', '¿Está seguro de quitar este usuario de la lista?', {
                        titulo: 'Confirmación',
                        onOk: () => {
                            usuariosSeleccionados = usuariosSeleccionados.filter(id => id !== String(userId));
                            userList.removeChild(li);
                            actualizarHiddenUsuarios();
                            mostrarAlerta('success', 'Usuario eliminado');
                        },
                        onCancel: () => {
                        }
                    });

                };

                userList.appendChild(li);
            });
        }

        actualizarHiddenUsuarios();

        // ROLES
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

    async function EjecutarEdicionTAC001(accion) {



        const formSelect = document.getElementById('modal-form-ref');
        const campoSelect = document.getElementById('modal-campo-ref');
        const operacionSelect = document.getElementById('modal-operacion');
        const formOrigenId = document.getElementById('formulario_id');


        const formDestinoId = accion.form_ref_id || formSelect.value; // 

        formSelect.value = accion.form_ref_id;
        formSelect.dispatchEvent(new Event('change'));

        formOrigenId.value = accion.form_origen_id;

        await cargarCamposCached(formDestinoId, campoSelect, '-- Seleccione campo destino --');
        campoSelect.value = accion.campo_ref_id;

        // Operación
        operacionSelect.value = accion.operacion;
        toggleInvertir();

        document.getElementById('tipo-valor').value = accion.tipo_valor;
        document.getElementById('tipo-valor').dispatchEvent(new Event('change'));

        if (accion.tipo_valor === 'static') {

            document.getElementById('modal-valor-estatico').value = accion.valor;
        } else {

            cambiarTipoValor(accion.valor);

        }

        const checkbox = document.getElementById("invertir_operacion");

        if (accion.operacion_rev == 1) {
            checkbox.checked = true;
        } else {
            checkbox.checked = false;
        }

    }

    async function abrirContenedorEdicion(accion, index) {

        document.getElementById('guardar-accion-logica').textContent = 'Guardar cambios';

        editingIndex = index;

        const tipoAccionSelect = document.getElementById('modal-tipo-accion');
        tipoAccionSelect.value = accion.tipo_accion_id;
        tipoAccionSelect.dispatchEvent(new Event('change'));

        await abrirContenedor()

        if (accion.tipo_accion_id === 'TAC-001') {

            EjecutarEdicionTAC001(accion);

            const container = document.getElementById('condiciones-container');

            if (!container) {
                return;
            }

            container.innerHTML = '';

            for (const c of accion.condiciones) {
                agregarCondicion(c);
            }
        }


        if (accion.tipo_accion_id === 'TAC-003') {

            EjecutarEdicionTAC003(accion);

        }

        if (accion.tipo_accion_id === 'TAC-006') {
            EjecutarEdicionTAC006(accion);

        }



    }

    async function EjecutarEdicionTAC006(accion) {

        const origen = document.getElementById('delete-formulario-origen');
        const destino = document.getElementById('delete-formulario-destino');

        origen.value = accion.form_origen_id;
        destino.value = accion.form_ref_id;

        const container =
            document.getElementById('delete-condiciones-container');

        container.innerHTML = '';

        const condiciones = Array.isArray(accion.condiciones)
            ? accion.condiciones
            : [];

        for (const condicion of condiciones) {

            await agregarCondicionDelete(condicion);

        }
    }
    function eliminarAccion(index) {

        if (index < 0 || index >= accionesArray.length) return;


        mostrarAlerta('confirm', '¿Está seguro de eliminar esta acción?', {
            titulo: 'Confirmar eliminación',
            onOk: () => {
                accionesArray.splice(index, 1);

                accionesJSONInput.value = JSON.stringify(accionesArray);

                accionesList.innerHTML = '';

                accionesArray.forEach((accion, i) => {
                    crearCardVisual(accion, i);
                });

                editingIndex = null;

                // 6️⃣ Mostrar mensaje vacío si ya no hay acciones
                if (accionesArray.length === 0) {
                    document.getElementById('contenedor_mensaje')?.classList.remove('d-none');
                    document.getElementById('contenedor_botones')?.classList.add('d-none');
                    document.getElementById('contenedor_condiciones')?.classList.add('d-none');
                }
                mostrarAlerta('success', 'Acción eliminada correctamente');
            },
            onCancel: () => {
            }
        });

    }


    async function abrirContenedor() {
        const nombreRegla = document.querySelector('input[name="nombre"]').value.trim();
        const FormularioDisparador = formularioDisparador.value;
        const evento = document.querySelector('select[name="evento"]').value;

        if (!nombreRegla) {

            mostrarAlerta('error', 'Ingrese el nombre de la regla');
            return;
        }
        if (!FormularioDisparador) {
            mostrarAlerta('error', 'Seleccione el formulario disparador');
            return;
        }
        if (!evento) {
            mostrarAlerta('error', 'Seleccione el evento');

            return;
        }


        const tipoAccionSelect = document.getElementById('modal-tipo-accion');

        if (tipoAccionSelect.value == "") {
            mostrarAlerta('warning', 'Seleccione un tipo de Acción');

            return
        }


        LimpiarContenedor();

        CargaTipoAccion()
    }


    document.addEventListener('change', async function (e) {

        if (!e.target.classList.contains('cond-campo')) return;

        const campoId = e.target.value;

        if (!campoId) return;

        try {
            const urlCamposDetalle = "{{ route('campos.detalle', ['campo_id' => ':id']) }}";
            const url = urlCamposDetalle.replace(':id', encodeURIComponent(campoId));

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.status) return;

            const opciones = data.opciones_catalogo;

            //CLAVE: SOLO el bloque actual
            const block = e.target.closest('.condicion-form-valor-block');

            const valorContainer = block.querySelector('.col-md-2:has(.cond-valor)')
                || block.querySelector('.cond-valor')?.closest('.col-md-2');

            const oldInput = block.querySelector('.cond-valor');

            if (!oldInput) return;

            //SI HAY OPCIONES → SELECT
            if (opciones && opciones.length > 0) {

                const select = document.createElement('select');
                select.classList.add('form-select', 'cond-valor');

                select.innerHTML = `<option value="">-- Seleccione --</option>`;

                opciones.forEach(op => {
                    select.innerHTML += `
                            <option value="${op.catalogo_codigo}">
                                ${op.catalogo_descripcion}
                            </option>
                        `;
                });

                oldInput.replaceWith(select);

            } else {

                const input = document.createElement('input');
                input.type = 'text';
                input.classList.add('form-control', 'cond-valor');

                oldInput.replaceWith(input);
            }

        } catch (error) {
            console.error(error);
        }
    });

    // Guardar acción desde el modal con validación

    document.getElementById('guardar-accion-logica')?.addEventListener('click', async () => {
        const tipoAccion = document.getElementById('modal-tipo-accion').value;
        if (!tipoAccion) {
            mostrarAlerta('warning', 'Seleccione un tipo de acción');
            return;
        }

        const accionObj = CapturarDatos();
        accionObj.filtros_relacion = Object.values(filtrosGuardados);

        if (tipoAccion === 'TAC-001') {

            const operacion = document.getElementById('modal-operacion').value;
            if (operacion == -1) {
                mostrarAlerta('warning', 'Seleccione un tipo de operación');
                return;
            }

            if (!accionObj.form_ref_id || !accionObj.campo_ref_id || !accionObj.operacion || !accionObj.valor) {

                mostrarAlerta('warning', 'Complete todos los campos obligatorios para la acción "Modificar Campo".');

                return;
            }
        }

        if (tipoAccion === 'TAC-005') {
            const usarRelacion = document.getElementById('check-usar-relacion')?.checked;
            const radioSeleccionado = document.querySelector('#formularios-relacionados input[name="listGroupRadio"]:checked');

            if (usarRelacion && !radioSeleccionado) {

                mostrarAlerta('warning', 'Seleccione un formulario relacionado.');

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
                    mostrarAlerta('warning', 'Seleccione un campo de origen para todas las filas marcadas.');

                    return;
                }

                if (!check?.checked && !estaBloqueadoPorRadio && inputDestino?.hasAttribute('required') && !inputDestino.value) {
                    mostrarAlerta('warning', 'Complete todos los campos obligatorios de destino.');

                    return;
                }
            }
        }

        if (tipoAccion === 'TAC-006') {

            if (!accionObj.condiciones?.length) {
                mostrarAlerta('warning', 'Debe agregar al menos una condición.');
                return;
            }

            if (!accionObj.form_ref_id) {
                mostrarAlerta('warning', 'Seleccione un formulario destino.');
                return;
            }

            if (!accionObj.form_origen_id) {
                mostrarAlerta('warning', 'Seleccione un formulario origen.');
                return;
            }

            for (const condicion of accionObj.condiciones) {

                if (!condicion.campo_condicion_origen) {
                    mostrarAlerta('warning', 'Seleccione un campo de referencia.');
                    return;
                }
                if (!condicion.operador) {
                    mostrarAlerta('warning', 'Seleccione una operación.');
                    return;
                }
                if (!condicion.tipo_valor) {
                    mostrarAlerta('warning', 'Seleccione el tipo de valor.');
                    return;
                }
                if (!condicion.campo_condicion_destino) {
                    mostrarAlerta('warning', 'Ingrese o seleccione un valor.');
                    return;
                }
                if (!condicion.mensaje) {
                    mostrarAlerta('warning', 'Ingrese un mensaje.');
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

                mostrarAlerta('warning', 'Seleccione al menos un usuario o un rol.');

                return;
            }

            // Asunto obligatorio
            if (!subject) {

                mostrarAlerta('warning', 'El asunto del correo es obligatorio.');

                document.getElementById('modal-email-subject').focus();
                return;
            }

            // Mensaje obligatorio
            if (!body) {

                mostrarAlerta('warning', 'El mensaje del correo es obligatorio.');

                document.getElementById('modal-email-body').focus();
                return;
            }


            // Plantilla obligatoria
            if (!plantilla) {

                mostrarAlerta('warning', 'Debe seleccionar una plantilla.');

                document.getElementById('email-template').focus();
                return false;
            }

        }

        if (tipoAccion != 'TAC-006') {

            let condicionesInvalidas = accionObj.condiciones.some(c => {


                if (c.tipo_condicion === 'form_valor') {

                    return (
                        !c.formulario_tipo ||
                        !c.campo_condicion ||
                        !c.operador ||
                        !c.valor
                    );
                }

                return (
                    !c.campo_condicion_origen ||
                    !c.operador ||
                    !c.campo_condicion_destino
                );

            });

            if (condicionesInvalidas) {

                mostrarAlerta('warning', 'Complete todos los campos de las condiciones.');

                return;
            }
        }

        if (editingIndex !== null) {
            accionesArray[editingIndex] = accionObj;
            accionesJSONInput.value = JSON.stringify(accionesArray);

            crearCardVisual(accionObj, editingIndex);
            editingIndex = null;
        } else {

            accionesArray.push(accionObj);
            accionesJSONInput.value = JSON.stringify(accionesArray);

            crearCardVisual(accionObj, accionesArray.length - 1);
        }

        restaurarInicio()
        LimpiarContenedor();
    });


    // abrir contenedor para agregar acción GENERAL

    document.getElementById('open-accion').addEventListener('click', async () => {

        await abrirContenedor()
    });







</script>