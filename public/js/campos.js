document.addEventListener('DOMContentLoaded', () => {

    /*** 1️⃣ Tooltips dinámicos ***/
    const initTooltips = () => {
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipElements.length) {
            tooltipElements.forEach(el => new bootstrap.Tooltip(el));
        }
    };

    /*** 2️⃣ Campo de formulario dinámico con info tooltip ***/
    const initCampoFormulario = () => {
        document.addEventListener('change', async (e) => {
            const el = e.target;
            if (!el.classList.contains('campo-formulario') || !el.dataset.formId) return;

            const formId = el.dataset.formId;
            const respuestaId = el.value;
            if (!respuestaId) return;

            try {
                const res = await fetch(`/formularios/${formId}/respuestas/${respuestaId}`);
                const data = await res.json();
                if (data.error) return console.warn("Error:", data.error);

                let tooltipHTML = '';
                const formularios = data.formularios ?? [{ nombre: 'Formulario: ' + data.nombre, datos: data.datos }];
                formularios.forEach(f => {
                    tooltipHTML += `<div class="text-start"><strong>${f.nombre}</strong><br>`;
                    for (const [campo, valor] of Object.entries(f.datos)) {
                        tooltipHTML += `${campo}: <em>${valor || '-'}</em><br>`;
                    }
                    tooltipHTML += `</div><hr>`;
                });

                const label = el.closest('div')?.querySelector('label');
                if (!label) return;

                let infoIcon = label.querySelector('.info-tooltip');
                if (!infoIcon) {
                    infoIcon = document.createElement('i');
                    infoIcon.className = 'fas fa-info-circle fa-lg text-primary ms-1 info-tooltip';
                    infoIcon.style.cursor = 'pointer';
                    label.appendChild(infoIcon);
                }

                infoIcon.setAttribute('data-bs-toggle', 'tooltip');
                infoIcon.setAttribute('data-bs-html', 'true');
                infoIcon.setAttribute('title', tooltipHTML);

                const existingTooltip = bootstrap.Tooltip.getInstance(infoIcon);
                if (existingTooltip) existingTooltip.dispose();
                new bootstrap.Tooltip(infoIcon);

            } catch (err) {
                console.error(err);
                if (typeof alertify !== 'undefined') alertify.error("Error al obtener datos del formulario");
            }
        });
    };

    /*** 3️⃣ Previsualización de imágenes y videos ***/
    const initPrevisualizacionMedia = () => {
        const previewMedia = (inputSelector, previewPrefix, tipo) => {
            document.querySelectorAll(inputSelector).forEach(input => {
                input.addEventListener('change', () => {
                    const previewDiv = document.getElementById(previewPrefix + input.name.replace('[]',''));
                    if (!previewDiv) return;
                    previewDiv.innerHTML = '';
                    Array.from(input.files).forEach(file => {
                        if(file.type.startsWith(tipo)) {
                            const reader = new FileReader();
                            reader.onload = e => {
                                if(tipo==='image'){
                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.style.maxWidth = '150px';
                                    img.classList.add('rounded', 'me-1', 'mb-1');
                                    previewDiv.appendChild(img);
                                } else if(tipo==='video'){
                                    const video = document.createElement('video');
                                    video.src = e.target.result;
                                    video.controls = true;
                                    video.style.maxWidth = '100%';
                                    video.style.height = 'auto';
                                    video.classList.add('mb-1');
                                    previewDiv.appendChild(video);
                                }
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });
            });
        };

        previewMedia('.preview-image', 'preview-', 'image');
        previewMedia('.preview-video', 'preview-video-', 'video');
    };

    /*** 4️⃣ Checkbox / Formulario Categorías ***/
    const initCheckboxFormulario = () => {
        const chkFormulario = document.getElementById('formulario_campo');
        const contCategorias = document.getElementById('categorias_cont');
        const contFormularios = document.getElementById('formularios_cont');
        if (!chkFormulario || !contCategorias || !contFormularios) return;

        const actualizarVisibilidad = () => {
            if (chkFormulario.checked) {
                contFormularios.style.display = 'block';
                contCategorias.style.display = 'none';
            } else {
                contFormularios.style.display = 'none';
                contCategorias.style.display = 'block';
            }
        };

        chkFormulario.addEventListener('change', actualizarVisibilidad);
        actualizarVisibilidad();
    };

    /*** 5️⃣ Drag & Drop Sortable ***/
    const initSortableCampos = () => {
        const lista = document.getElementById('listaCampos');
        if (!lista || typeof Sortable === 'undefined') return;

        new Sortable(lista, {
            animation: 150,
            handle: '.drag-handle',
            draggable: '.col-md-6',
            onEnd: function () {
                let orden = [...lista.querySelectorAll('.col-md-6')].map(item => item.dataset.id);
                fetch(window.routes.reordenarCampos, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ orden })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && typeof alertify !== 'undefined') alertify.success('Orden guardado correctamente');
                    else if(typeof alertify !== 'undefined') alertify.error('Error al guardar el orden');
                });
            }
        });
    };

    /*** 6️⃣ Edición y cancelación de campos ***/
    const initEdicionCampos = () => {
        let editId = null;
        const btnCancelar = document.getElementById('btnCancelarEdicion');

        if(btnCancelar){
            btnCancelar.addEventListener('click', () => {
                if(typeof alertify !== 'undefined') alertify.error("Edición cancelada");
                editId = null;

                const form = document.getElementById('formCampo');
                if(!form) return;

                form.action = form.dataset.storeUrl || form.action;
                form.querySelector('input[name="_method"]')?.remove();

                ['tipoCampo','nombreCampo','etiquetaCampo'].forEach(id=>{
                    const el = document.getElementById(id);
                    if(el) el.value = '';
                });
                const requeridoCampo = document.getElementById('requeridoCampo');
                if(requeridoCampo) requeridoCampo.checked = false;

                const catCampo = document.getElementById('ContenedorCategoria');
                if(catCampo){
                    catCampo.style.display = 'none';
                    catCampo.value = '';
                }

                btnCancelar.style.display = 'none';
                const btnAgregar = document.getElementById('btnAgregarCampo');
                if(btnAgregar) btnAgregar.textContent = 'Agregar campo';
            });
        }

        const cargarCampo = async (id) => {
            try{
                const res = await fetch(`/campos/${id}`);
                const data = await res.json();
                if(!data.success) return typeof alertify !== 'undefined' && alertify.error('No se pudo cargar el campo');

                const campo = data.campo;
                document.getElementById('tipoCampo').value = campo.tipo;
                document.getElementById('nombreCampo').value = campo.nombre;
                document.getElementById('etiquetaCampo').value = campo.etiqueta;
                document.getElementById('requeridoCampo').checked = campo.requerido;

                const catCampo = document.getElementById('ContenedorCategoria');
                if(campo.categoria_id){
                    catCampo.style.display = 'inline-block';
                    catCampo.value = campo.categoria_id;
                } else if(campo.form_ref_id){
                    document.getElementById('formulario_campo').checked = true;
                    catCampo.style.display = 'inline-block';
                    initCheckboxFormulario(); // actualizar visibilidad
                } else {
                    catCampo.style.display = 'none';
                    catCampo.value = '';
                }

                const form = document.getElementById('formCampo');
                form.action = `/campos/${id}`;
                form.method = 'POST';

                let inputMethod = form.querySelector('input[name="_method"]');
                if(!inputMethod){
                    inputMethod = document.createElement('input');
                    inputMethod.type = 'hidden';
                    inputMethod.name = '_method';
                    form.appendChild(inputMethod);
                }
                inputMethod.value = 'PUT';

                const btnAgregar = document.getElementById('btnAgregarCampo');
                btnAgregar.textContent = 'Actualizar campo';
                if(btnCancelar) btnCancelar.style.display = 'inline-block';
                if(typeof alertify !== 'undefined') alertify.success("Ahora puede editar el campo seleccionado");

            }catch(err){
                console.error(err);
                if(typeof alertify !== 'undefined') alertify.error('Error al cargar el campo');
            }
        };

        document.querySelectorAll('.btnEditarCampo').forEach(btn=>{
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                try{
                    const res = await fetch(`/campos/${id}/check-respuestas`);
                    const check = await res.json();
                    if(check.tiene_respuestas && typeof alertify !== 'undefined'){
                        alertify.confirm(
                            'Advertencia',
                            'Este campo pertenece a un formulario que ya tiene respuestas. ¿Seguro que quieres editarlo?',
                            ()=> cargarCampo(id),
                            ()=> alertify.error('Edición cancelada')
                        );
                    } else cargarCampo(id);
                }catch{
                    if(typeof alertify !== 'undefined') alertify.error('Error al verificar respuestas del formulario');
                }
            });
        });

        window.eliminarCampo = (idForm, idCampo) => {
            fetch(`/campos/${idCampo}/check-respuestas`)
            .then(r=>r.json())
            .then(check=>{
                const confirmar = (title, callback)=>{
                    if(typeof alertify !== 'undefined') alertify.confirm(title,'¿Estás seguro?', callback, ()=>alertify.error('Cancelado'));
                    else callback();
                };
                if(check.tiene_respuestas) confirmar('Este campo tiene respuestas. ¿Eliminar?', ()=>document.getElementById(idForm)?.submit());
                else confirmar('Confirmar eliminación', ()=>document.getElementById(idForm)?.submit());
            }).catch(()=>typeof alertify !== 'undefined' && alertify.error('Error al verificar respuestas'));
        };
    };

    /*** 7️⃣ TomSelect + radios + checkboxes + búsqueda ***/
    const initSelectsYOpciones = () => {
        const initCargarMasSelect = (select) => {
            let offset = select.options.length;
            const limit = 20;
            const ts = new TomSelect(select, {
                maxOptions: 100,
                plugins: ['dropdown_input'],
                render: { option: (item, escape)=>`<div class="${item.nueva?'option-nueva':''}">${escape(item.text)}</div>` },
                onDropdownOpen: function(){
                    const dropdown = this.dropdown;
                    if(!dropdown.querySelector('.ts-dropdown-ver-mas')){
                        const btnVerMas = document.createElement('div');
                        btnVerMas.classList.add('ts-dropdown-ver-mas','text-center','p-1');
                        btnVerMas.innerHTML = `<button type="button" class="btn btn-sm btn-outline-primary w-100">Ver más...</button>`;
                        btnVerMas.querySelector('button').addEventListener('click', async ()=>{
                            const campoId = select.dataset.campoId;
                            const resp = await fetch(`/campos/${campoId}/cargar-mas?offset=${offset}&limit=${limit}`);
                            const data = await resp.json();
                            data.forEach(opcion=>{
                                opcion.nueva=true;
                                ts.addOption({value:opcion.catalogo_codigo,text:opcion.catalogo_descripcion,nueva:true});
                            });
                            ts.refreshOptions(false);
                            setTimeout(()=>{ data.forEach(o=>{ const opt=ts.getOption(o.catalogo_codigo); if(opt) opt.classList.remove('option-nueva'); }); },1000);
                            offset += data.length;
                        });
                        dropdown.appendChild(btnVerMas);
                    }
                }
            });
        };

        document.querySelectorAll('.tom-select').forEach(initCargarMasSelect);

        // Radios y checkboxes dinámicos
        const initCargarMasRadioCheckbox = (selector, tipo) => {
            const offsets = {};
            document.querySelectorAll(selector).forEach(container=>{
                const campoId = container.dataset.campoId;
                offsets[campoId] = container.querySelectorAll('input[type="'+tipo+'"]').length;
                const btnVerMas = container.querySelector('.btn-ver-mas, .btn-ver-mas-checkbox');
                if(!btnVerMas) return;
                btnVerMas.addEventListener('click', async ()=>{
                    const offset = offsets[campoId];
                    const limit = 20;
                    const response = await fetch(`/campos/${campoId}/cargar-mas?offset=${offset}&limit=${limit}`);
                    const data = await response.json();
                    data.forEach(opcion=>{
                        const div = document.createElement('div');
                        div.classList.add('form-check','option-nueva');
                        if(tipo==='radio'){
                            div.innerHTML = `<input type="radio" name="campo_${campoId}" value="${opcion.catalogo_codigo}" class="form-check-input" id="campo_${campoId}_${opcion.catalogo_codigo}">
                                             <label class="form-check-label" for="campo_${campoId}_${opcion.catalogo_codigo}">${opcion.catalogo_descripcion}</label>`;
                            container.insertBefore(div, btnVerMas);
                        } else {
                            div.innerHTML = `<input type="checkbox" name="${btnVerMas.dataset.campoNombre}[]" value="${opcion.catalogo_codigo}" class="form-check-input" id="${btnVerMas.dataset.campoNombre}_${opcion.catalogo_codigo}">
                                             <label class="form-check-label" for="${btnVerMas.dataset.campoNombre}_${opcion.catalogo_codigo}">${opcion.catalogo_descripcion}</label>`;
                            container.appendChild(div);
                        }
                        setTimeout(()=>div.classList.remove('option-nueva'),1000);
                    });
                    offsets[campoId] += data.length;
                });
            });
        };
        initCargarMasRadioCheckbox('.radio-container','radio');
        initCargarMasRadioCheckbox('.opciones-container','checkbox');

        // Búsqueda en modal
        document.querySelectorAll('.btn-buscar-opcion').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const campoId = btn.dataset.campoId;
                const modalEl = document.getElementById('modalBusqueda');
                if(modalEl) new bootstrap.Modal(modalEl).show();
                document.getElementById('btnBuscar').dataset.campoId = campoId;
            });
        });

        const btnBuscar = document.getElementById('btnBuscar');
        if (btnBuscar) {

            btnBuscar.addEventListener('click', async () => {
        
                const termino = document.getElementById('inputBusqueda')?.value || '';
                const campoId = btnBuscar.dataset.campoId;
        
                const response = await fetch(
                    `/campos/${campoId}/buscar-opcion?termino=${encodeURIComponent(termino)}`
                );
        
                const data = await response.json();
        
                if (data.length) {

                    const select = document.querySelector(
                        `select[data-campo-id="${campoId}"]`
                    );
                
                    if (select && select.tomselect) {
                
                        const tom = select.tomselect;
                
                        // 🔥 Obtener el orden mínimo actual
                        const ordenMinimo = Math.min(
                            ...Object.values(tom.options).map(o => o.$order ?? 0),
                            0
                        );
                
                        let nuevoOrden = ordenMinimo - 1;
                
                        const agregados = [];
                
                        data.forEach(item => {
                
                            if (!tom.options[item.catalogo_codigo]) {
                
                                tom.addOption({
                                    value: item.catalogo_codigo,
                                    text: item.catalogo_descripcion,
                                    $order: nuevoOrden--,
                                    nueva: true
                                });
                
                                agregados.push(item.catalogo_codigo);
                            }
                
                        });
                
                        tom.refreshOptions(false);
                
                        // 🔥 Aplicar clase visual
                        setTimeout(() => {
                            agregados.forEach(codigo => {
                                const opcion = tom.getOption(codigo);
                                if (opcion) {
                                    opcion.classList.add('option-nueva');
                                }
                            });
                        }, 50);
                
                        // 🔥 Quitar clase después de 1 segundo
                        setTimeout(() => {
                            agregados.forEach(codigo => {
                                const opcion = tom.getOption(codigo);
                                if (opcion) {
                                    opcion.classList.remove('option-nueva');
                                }
                            });
                        }, 3000);
                    }
                
                    if (typeof alertify !== 'undefined') {
                        alertify.success('Opciones encontradas agregadas arriba. Revise y seleccione.');
                    }
                
                    const modal = document.getElementById('modalBusqueda');
                    if (modal) bootstrap.Modal.getInstance(modal)?.hide();
                
                } else {
                
                    if (typeof alertify !== 'undefined') {
                        alertify.warning('No se encontró ninguna opción.');
                    }
                
                }
        
            });
        
        }
    };

    // ---- Inicialización de todos los módulos ----
    initTooltips();
    initCampoFormulario();
    initPrevisualizacionMedia();
    initCheckboxFormulario();
    initSortableCampos();
    initEdicionCampos();
    initSelectsYOpciones();

});
