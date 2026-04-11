<div class="modal fade" id="modalVerRespuesta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Registro</h5>

                <i id="tooltip-registro-multiple" class="fas fa-info-circle text-white d-none">
                </i>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoRespuesta">
                    <div class="text-center">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.btn-ver-respuesta').forEach(btn => {
            btn.addEventListener('click', function (e) {

                e.preventDefault();

                const tooltipIcon = document.getElementById('tooltip-registro-multiple');
                const formId = this.dataset.formId;
                const respuestaId = this.dataset.respuestaId;
                const modal = new bootstrap.Modal(document.getElementById('modalVerRespuesta'));
                const contenido = document.getElementById('contenidoRespuesta');

                tooltipIcon.classList.add('d-none');

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
                };

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
                };

                fetch(`/formularios/${formId}/respuestas/${respuestaId}/visor`)
                    .then(response => response.json())
                    .then(data => {

                        if (data.error) {
                            contenido.innerHTML = `<div class="alert alert-danger p-1 small">${data.error}</div>`;
                            return;
                        }

                        let html = `<div class="row g-1">`;
                        html += renderCampos(data.campos);
                        html += `</div>`;

                        if (data.respuestas_grupo && data.respuestas_grupo.length > 0) {

                            tooltipIcon.classList.remove('d-none');
                            tooltipIcon.classList.add('d-inline');

                            new bootstrap.Tooltip(tooltipIcon, {
                                title: "Registro múltiple",
                                placement: "top"
                            });

                            html += `<hr class="my-1">
                                 <div class="small fw-bold mb-1">Grupo:</div>`;

                            html += `<div class="table-responsive" style="max-height:220px;">
                            <table class="table table-sm table-bordered mb-0 align-middle text-center">
                                <thead class="table-dark small">
                                    <tr>`;

                            const encabezados = data.respuestas_grupo[0].campos.map(c => c.etiqueta);
                            encabezados.forEach(enc => html += `<th class="p-1">${enc}</th>`);

                            html += `</tr></thead><tbody class="small">`;

                            data.respuestas_grupo.forEach(resp => {
                                html += `<tr>`;
                                resp.campos.forEach(campo => {
                                    let valor = '';
                                    campo.valores.forEach(v => valor += renderValor(campo.tipo, v));
                                    if (!valor) valor = '—';
                                    html += `<td class="p-1">${valor}</td>`;
                                });
                                html += `</tr>`;
                            });

                            html += `</tbody></table></div>`;
                        }

                        if (data.datos_asociados) {

                            html += `<hr class="my-1">
                            <div class="small fw-bold mb-1">Datos asociados:</div>`;

                            html += `<div class="table-responsive">
                                <table class="table table-sm table-bordered mb-1 text-center align-middle">
                                    <thead class="table-light small">
                                        <tr>`;

                            // encabezados
                            Object.keys(data.datos_asociados).forEach(key => {
                                html += `<th class="p-1 text-uppercase">${key.replaceAll('_', ' ')}</th>`;
                            });

                            html += `</tr></thead><tbody class="small"><tr>`;

                            // valores
                            Object.values(data.datos_asociados).forEach(valor => {
                                html += `<td class="p-1">${valor ?? '—'}</td>`;
                            });

                            html += `</tr></tbody></table></div>`;
                        }

                        contenido.innerHTML = html;
                        modal.show();
                    });

            });
        });

    });
</script>