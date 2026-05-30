   
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
        await abrirContenedorEdicion(accionObj, index);
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

    if (accionObj.tipo_accion_id === 'TAC-006') {

        let condicionesHtml = '';

        (accionObj.condiciones || []).forEach((condicion, index) => {

            condicionesHtml += `

            <div class="border rounded p-2 mb-2 bg-light">

                <strong>Condición ${index + 1}</strong><br>

                <strong>Formulario:</strong>
                ${condicion.form_ref_text || ''}<br>

                <strong>Campo:</strong>
                ${condicion.campo_ref_text || ''}<br>

                <strong>Operación:</strong>
                ${condicion.operacion_text || condicion.operacion}<br>

                <strong>Tipo valor:</strong>
                ${condicion.tipo_valor}<br>

                <strong>Valor:</strong>
                ${condicion.valor_text || condicion.valor}

                <strong>Mensaje:</strong>
                ${condicion.mensaje}
                
            </div>

        `;

        });

        contenido += `${condicionesHtml}`;
    }

    if (accionObj.tipo_accion_id != 'TAC-006') {
        if (accionObj.condiciones?.length) {
            contenido += `<hr><strong>Condiciones:</strong><br>`;

            accionObj.condiciones.forEach((c, i) => {

                const condicionTexto = `${c.campo_condicion_origen_text} ${c.operador_text || c.operador} ${c.campo_condicion_destino_text}`;

                if (c.mensaje && c.mensaje.trim() !== '') {
                    contenido += `
                                <div class="mb-2">
                                    <div><strong>Condición ${i + 1}:</strong></div>
                                    <div class="text-danger">${c.mensaje}</div>
                                    <small class="text-muted">(${condicionTexto})</small>
                                </div>
                            `;
                } else {
                    contenido += `
                                <div class="mb-2">
                                    <strong>Condición ${i + 1}:</strong>
                                    ${condicionTexto}
                                </div>
                            `;
                }

            });
        }

    }
    return contenido;
}

