    const camposCache = { origen: {}, destino: {} };




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

