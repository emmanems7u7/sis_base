    // Función para agregar condición
    function agregarCondicion(condData = null) {

        const template = document.getElementById('condicion-modal-template').content.cloneNode(true);
        const container = template.querySelector('.condicion-block');
        const selectOrigen = container.querySelector('.cond-form-origen');
        const selectDestino = container.querySelector('.cond-form-destino');

        // cargar campos cache
        const formOrigenId = document.querySelector('.select-formulario').value;
        const formDestinoId = document.getElementById('modal-form-ref').value;
        cargarCamposCached(formOrigenId, selectOrigen, '-- Seleccione campo origen --')
        .then(() => {
            if (condData?.campo_condicion_origen) {
                selectOrigen.value = String(condData.campo_condicion_origen);
            }
        });
    
        cargarCamposCached(formDestinoId, selectDestino, '-- Seleccione campo destino --')
            .then(() => {
                if (condData?.campo_condicion_destino) {
                    selectDestino.value = String(condData.campo_condicion_destino);
                }
            });
        
        // precargar operador
        if (condData?.operador) {
            container.querySelector('.cond-operador').value = condData.operador;
        }
        if (condData?.mensaje) {
            container.querySelector('.cond-mensaje').value = condData.mensaje;
        }

        container.querySelector('.remove-condicion-modal')
            .addEventListener('click', () => {

                mostrarAlerta('confirm', '¿Estás seguro de que deseas eliminar esta condición?', {
                    titulo: 'Eliminar condición',
                    onOk: () => {
                        container.remove();
                        mostrarAlerta('success', 'Condición eliminada');
                    },
                    onCancel: () => {

                    }
                });


            });
        document.getElementById('condiciones-container').appendChild(container);
    }

        // =========================================
        // AGREGAR CONDICIÓN
        // VALOR FORM ORIGEN / DESTINO
        // =========================================
        function agregarCondicionFormValor(condData = null) {

            const template = document.getElementById('condicion-form-valor-template').content.cloneNode(true);
            const container = template.querySelector('.condicion-form-valor-block');
            const selectTipo = container.querySelector('.cond-tipo-formulario');
            const selectCampo = container.querySelector('.cond-campo');
            const selectOperador = container.querySelector('.cond-operador');
            const inputValor = container.querySelector('.cond-valor');
            const preview = container.querySelector('.preview-condicion');
            const hidden = container.querySelector('.condicion-config-hidden');

            // CAMBIO FORMULARIO
            selectTipo.addEventListener('change', function () {

                const formOrigenId = document.querySelector('.select-formulario').value;
                const formDestinoId = document.getElementById('modal-form-ref').value;
                let formId = '';
                // origen
                if (this.value === 'origen') {
                    formId = formOrigenId;
                }

                // destino
                if (this.value === 'destino') {
                    formId = formDestinoId;
                }

                // limpiar
                selectCampo.innerHTML = '';

                // cargar campos
                cargarCamposCached(formId, selectCampo, '-- Seleccione --');

            });


            // =====================================
            // ELIMINAR
            // =====================================
            container.querySelector('.remove-condicion-form-valor')
                .addEventListener('click', () => {

                    mostrarAlerta(
                        'confirm',
                        '¿Eliminar condición?',
                        {

                            titulo: 'Eliminar',

                            onOk: () => {

                                container.remove();

                                mostrarAlerta(
                                    'success',
                                    'Condición eliminada'
                                );

                            }

                        }
                    );

                });

            // =====================================
            // APPEND
            // =====================================
            document.getElementById(
                'condiciones-container'
            ).appendChild(container);

        }

        async function AgregarCondicionFormRelacion(condData = null) {

            const template = document.getElementById('condicion-modal-relacion-template').content .cloneNode(true);
        
            const container = template.querySelector('.condicion-block');
        
            const selectRelacion = container.querySelector('.cond-form-relacion');
            const selectOrigen   = container.querySelector('.cond-form-origen');
            const selectDestino  = container.querySelector('.cond-form-destino');
        
            const formOrigenId  = document.querySelector('.select-formulario').value;
            const formDestinoId = document.getElementById('modal-form-ref').value;
        
            // ============================
            // Cargar formularios relacionados
            // ============================
        
            await cargarFormulariosRelacionados(formOrigenId, selectRelacion);
        
            // Si estamos editando
            if (condData?.formulario_relacion_origen) {
        
                selectRelacion.value = String(condData.formulario_relacion_origen);
        
                await cargarCamposCached(
                    condData.formulario_relacion_origen,
                    selectOrigen,
                    '-- Seleccione campo origen --'
                );
        
                if (condData?.campo_condicion_origen) {
                    selectOrigen.value = String(condData.campo_condicion_origen);
                }
            }
        
            // Cambio de formulario relacionado
            selectRelacion.addEventListener('change', async function () {
        
                selectOrigen.innerHTML =
                    '<option value="">-- Seleccione campo origen --</option>';
        
                if (!this.value) return;
        
                await cargarCamposCached(
                    this.value,
                    selectOrigen,
                    '-- Seleccione campo origen --'
                );
        
            });
        
            // ============================
            // Campo destino (igual que antes)
            // ============================
        
            await cargarCamposCached(
                formDestinoId,
                selectDestino,
                '-- Seleccione campo destino --'
            );
        
            if (condData?.campo_condicion_destino) {
                selectDestino.value = String(condData.campo_condicion_destino);
            }
        
            // ============================
            // Operador
            // ============================
        
            if (condData?.operador) {
                container.querySelector('.cond-operador').value = condData.operador;
            }
        
            // ============================
            // Mensaje
            // ============================
        
            if (condData?.mensaje) {
                container.querySelector('.cond-mensaje').value = condData.mensaje;
            }
        
            // ============================
            // Eliminar
            // ============================
        
            container.querySelector('.remove-condicion-modal')
                .addEventListener('click', () => {
        
                    mostrarAlerta(
                        'confirm',
                        '¿Estás seguro de que deseas eliminar esta condición?',
                        {
                            titulo: 'Eliminar condición',
        
                            onOk: () => {
                                container.remove();
                                mostrarAlerta('success', 'Condición eliminada');
                            },
        
                            onCancel: () => {}
                        }
                    );
        
                });
        
            document
                .getElementById('condiciones-container')
                .appendChild(container);
        
        }


        async function cargarFormulariosRelacionados(formularioId, select){

            select.innerHTML = '<option value="">Formulario relacionado</option>';
        
            if(!formularioId) return;
        
            const response = await fetch(`/formularios/${formularioId}/relacionados`);
        
            const formularios = await response.json();
        
            formularios.forEach(f=>{
        
                const option=document.createElement('option');
        
                option.value=f.id;
        
                option.textContent=f.nombre;
        
                select.appendChild(option);
        
            });
        
        }

     // CASOS PARA AGREGAR VALIDACIONES
     document.getElementById('btn-agregar-condicion').addEventListener('click', () => {
        const selector = document.getElementById('selector-condicion-modal');
        const tipo = selector.value;

        switch (tipo) {

            case 'OP-001':

                agregarCondicion();
                break;

            case 'OP-002':


                break;

            case 'OP-003':

                agregarCondicionFormValor();
                break;

            case 'OP-004':

                break;
            case 'OP-005':
                AgregarCondicionFormRelacion();
                break;
        }

    });
