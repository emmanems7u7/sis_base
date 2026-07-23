<div class="modal fade" id="modalVerRespuesta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div
            class="modal-content shadow border-0 rounded-4
            {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : '' }}">

            <div class="modal-header py-2 px-3">
                <h6 class="modal-title fw-semibold flex-grow-1" id="modal_titulo">
                    Información
                </h6>

                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body p-3" id="contenidoRespuesta">
                <!-- Contenido dinámico -->
            </div>

        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.addEventListener('click', function(e) {

            const btn = e.target.closest('.btn-ver-respuesta');

            if (!btn) return;


            e.preventDefault();

            const formId = btn.dataset.formId;
            const respuestaId = btn.dataset.respuestaId;

            const modal = new bootstrap.Modal(document.getElementById('modalVerRespuesta'));
            const contenido = document.getElementById('contenidoRespuesta');



            function renderValor(tipo, valor) {
                switch (tipo) {
                    case 'imagen':
                        return `<a href="${valor}" data-fancybox="gallery" class="ver-link small">
                <i class="fas fa-image fs-7"></i>
            </a>`;
                    case 'video':
                        return `<a href="${valor}" target="_blank" class="small">
                <i class="fas fa-video fs-7"></i>
            </a>`;
                    case 'archivo':
                        return `<a href="${valor}" target="_blank" class="small">
                <i class="fas fa-file fs-7"></i>
            </a>`;
                    case 'enlace':
                        return `<a href="${valor}" target="_blank" class="text-decoration-none small d-block text-truncate">
                <i class="fas fa-link fs-7"></i>
            </a>`;
                    case 'color':
                        return `<span class="d-inline-flex align-items-center small">
                <span style="width:12px;height:12px;border-radius:3px;background:${valor};margin-right:3px;"></span>
            </span>`;
                    default:
                        return `<span class="small">${valor ?? '—'}</span>`;
                }
            }

            function renderCampos(campos) {
                let html = '';

                campos.forEach(campo => {

                    let valoresHtml = '';
                    campo.valores.forEach(v => {
                        valoresHtml += renderValor(campo.tipo, v);
                    });

                    if (!valoresHtml) valoresHtml = '<span class="text-muted small">—</span>';

                    html += `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="mb-1 p-1 border-bottom">
                    <div class="text-uppercase small text-muted lh-1">${campo.etiqueta}</div>
                    <div class="fw-medium small lh-1">${valoresHtml}</div>
                </div>
            </div>`;
                });

                return html;
            }

            fetch(`/formularios/${formId}/respuestas/${respuestaId}/visor`)
                .then(r => r.json())
                .then(data => {

                    if (data.error) {
                        contenido.innerHTML =
                            `<div class="alert alert-danger p-1 small">${data.error}</div>`;
                        return;
                    }

                    let html = `<div class="row g-1">`;
                    html += renderCampos(data.campos);
                    html += `</div>`;

                    var titulo_modal = document.getElementById('modal_titulo');
                    titulo_modal.innerHTML = "";

                    titulo_modal.innerHTML = data.detalle_registro;

                    if (data.respuestas_grupo && data.respuestas_grupo.length > 0) {



                        html += `<hr class="my-1">
                     <div class="small fw-bold mb-1">` + data.grupo_title + `</div>`;

                        html += `<div class="table-responsive" style="max-height:220px;">
                        <table class="table table-sm table-bordered mb-0 align-middle text-center">
                            <thead class="table-dark small"><tr>`;

                        const encabezados = data.respuestas_grupo[0].campos.map(c => c.etiqueta);
                        encabezados.forEach(enc => html += `<th class="p-1">${enc}</th>`);

                        html += `</tr></thead><tbody class="small">`;

                        data.respuestas_grupo.forEach(resp => {
                            html += `<tr>`;
                            resp.campos.forEach(campo => {
                                let valor = '';
                                campo.valores.forEach(v => valor += renderValor(
                                    campo.tipo, v));
                                if (!valor) valor = '—';
                                html += `<td class="p-1">${valor}</td>`;
                            });
                            html += `</tr>`;
                        });

                        html += `</tbody></table></div>`;
                    }

                    if (data.datos_asociados && Object.keys(data.datos_asociados).length > 0) {

                        html += `<hr class="my-1">
                     <div class="small fw-bold mb-1">` + data.datos_asociados_title + `</div>`;

                        html += `<div class="table-responsive">
                        <table class="table table-sm table-bordered mb-1 text-center align-middle">
                            <thead class="table-dark small"><tr>`;

                        Object.keys(data.datos_asociados).forEach(key => {
                            html +=
                                `<th class="p-1 text-uppercase">${key.replaceAll('_', ' ')}</th>`;
                        });

                        html += `</tr></thead><tbody class="small"><tr>`;

                        Object.values(data.datos_asociados).forEach(valor => {
                            html += `<td class="p-1">${valor ?? '—'}</td>`;
                        });

                        html += `</tr></tbody></table></div>`;
                    }

                    if (data.datos_relacionados && data.datos_relacionados.length > 0) {

                        html += `<hr class="my-1">
    <div class="small fw-bold mb-1">
        ` + data.datos_relacionados_title + `
    </div>`;

                        const encabezados = data.datos_relacionados[0].campos.map(c => c.etiqueta);

                        html += `
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0 align-middle text-center">
            <thead class="table-light small">
                <tr>
`;

                        encabezados.forEach(enc => {
                            html += `<th class="p-1">${enc}</th>`;
                        });

                        html += `
                </tr>
            </thead>
            <tbody class="small">
`;

                        data.datos_relacionados.forEach(registro => {

                            html += `<tr>`;

                            registro.campos.forEach(campo => {

                                let valor = '';

                                campo.valores.forEach(v => {
                                    valor += renderValor(campo.tipo, v);
                                });

                                html += `<td class="p-1">${valor || '—'}</td>`;
                            });

                            html += `</tr>`;
                        });

                        html += `
            </tbody>
        </table>
    </div>
`;
                    }

                    contenido.innerHTML = html;
                    const offcanvasEl = document.getElementById('offcanvasAcciones');

                    if (offcanvasEl) {
                        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                        offcanvas.hide();
                    }
                    modal.show();
                });

        });

    });
</script>
