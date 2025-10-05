<!-- Modal -->
<div class="modal fade" id="modalFormulario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloFormulario">Formulario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p id="descripcionFormulario" class="text-muted"></p>

                <form id="formCampos" class="row g-3"></form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.ver-formulario').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                fetch(`/formulario/${id}/campos`)
                    .then(res => res.json())
                    .then(data => {
                        // Título y descripción del formulario
                        document.getElementById('tituloFormulario').innerText = data.nombre;
                        document.getElementById('descripcionFormulario').innerText = data.descripcion;

                        const form = document.getElementById('formCampos');
                        form.innerHTML = '<div class="row g-4"></div>';

                        const row = form.querySelector('.row');

                        // ✅ Ordenar campos por posición antes de renderizar
                        data.campos.sort((a, b) => a.posicion - b.posicion);

                        data.campos.forEach(campo => {
                            let inputHtml = '';
                            const requerido = campo.requerido ? 'required' : '';
                            const placeholder = campo.config?.placeholder ?? '';

                            switch (campo.campo_nombre.toLowerCase()) {
                                case 'text':
                                    inputHtml = `
                                    <input type="text" class="form-control" placeholder="${placeholder}" ${requerido}>
                                `;
                                    break;

                                case 'number':
                                    inputHtml = `
                                    <input type="number" class="form-control" placeholder="${placeholder}" ${requerido}>
                                `;
                                    break;

                                case 'textarea':
                                    inputHtml = `
                                    <textarea class="form-control" placeholder="${placeholder}" ${requerido}></textarea>
                                `;
                                    break;

                                case 'checkbox':
                                    if (campo.opciones_catalogo?.length) {
                                        inputHtml = campo.opciones_catalogo.map(op => `
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="${campo.nombre}_${op.catalogo_codigo}">
                                            <label class="form-check-label" for="${campo.nombre}_${op.catalogo_codigo}">
                                                ${op.catalogo_descripcion}
                                            </label>
                                        </div>
                                    `).join('');
                                    }
                                    break;

                                case 'radio':
                                    if (campo.opciones_catalogo?.length) {
                                        inputHtml = campo.opciones_catalogo.map(op => `
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="${campo.nombre}" id="${campo.nombre}_${op.catalogo_codigo}">
                                            <label class="form-check-label" for="${campo.nombre}_${op.catalogo_codigo}">
                                                ${op.catalogo_descripcion}
                                            </label>
                                        </div>
                                    `).join('');
                                    }
                                    break;

                                case 'selector':
                                    if (campo.opciones_catalogo?.length) {
                                        inputHtml = `
                                        <select class="form-select" ${requerido}>
                                            <option value="">Seleccione una opción</option>
                                            ${campo.opciones_catalogo.map(op => `
                                                <option value="${op.catalogo_codigo}">${op.catalogo_descripcion}</option>
                                            `).join('')}
                                        </select>
                                    `;
                                    } else {
                                        inputHtml = `
                                        <select class="form-select" disabled>
                                            <option>No hay opciones disponibles</option>
                                        </select>
                                    `;
                                    }
                                    break;

                                default:
                                    inputHtml = `
                                    <input type="text" class="form-control" placeholder="${placeholder}" ${requerido}>
                                `;
                            }

                            // ✅ Si el campo es textarea, que ocupe toda la fila
                            const colClass = campo.campo_nombre.toLowerCase() === 'textarea' ? 'col-12' : 'col-md-6';

                            row.innerHTML += `
                            <div class="${colClass}">
                                <label class="form-label fw-bold">${campo.etiqueta} ${campo.requerido ? '<span class="text-danger">*</span>' : ''}</label>
                                ${inputHtml}
                            </div>
                        `;
                        });

                        new bootstrap.Modal(document.getElementById('modalFormulario')).show();
                    })
                    .catch(err => console.error('Error al cargar campos:', err));
            });
        });
    });
</script>