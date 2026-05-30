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
                            <button type="button" class="btn btn-secondary" id="btn-filtrar-relacion">Filtrar</button>
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

                                    mostrarAlerta('error', 'Complete todos los campos del filtro.');
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


                                    mostrarAlerta('confirm', '¿Está seguro de que desea eliminar este filtro?', {
                                        titulo: 'Eliminar filtro',
                                        onOk: () => {
                                            delete filtrosGuardados[filtroId]; // eliminar del objeto
                                            badge.remove();
                                            mostrarAlerta('success', 'Filtro eliminado');
                                        },
                                        onCancel: () => {
                                        }
                                    });


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
        const formOrigenSelect = document.querySelector('select[name="formulario_id_disparador"]');
        const formOrigenId = formOrigenSelect ? formOrigenSelect.value : null;

        // Llamada a la función que construye los campos con soporte para checkbox + selector de origen
        construirCamposFormulario(formularioDestino, contenedorCampos, formOrigenId);
    }
});
