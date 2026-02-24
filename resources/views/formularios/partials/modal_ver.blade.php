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

                // Función para generar HTML de un valor según su tipo
                function renderValor(tipo, valor) {
                    switch (tipo) {
                        case 'imagen':
                            return `<img src="${valor}" class="img-fluid rounded mb-1" style="max-height:100px;">`;
                        case 'video':
                            return `<video src="${valor}" controls class="w-100 rounded mb-1" style="max-height:120px;"></video>`;
                        case 'archivo':
                            return `<a href="${valor}" target="_blank" class="d-inline-block mb-1 text-decoration-none">📎 Descargar archivo</a>`;
                        case 'enlace':
                            return `<a href="${valor}" target="_blank" class="d-block mb-1 text-decoration-none">🔗 ${valor}</a>`;
                        case 'color':
                            return `<div class="d-flex align-items-center mb-1">
                                <span style="width:16px;height:16px;border-radius:4px;background:${valor};margin-right:4px;"></span>
                                ${valor}
                            </div>`;
                        default:
                            return `<div class="mb-1">${valor ?? '—'}</div>`;
                    }
                };

                // renderizar campos de una respuesta
                function renderCampos(campos) {
                    let html = '';
                    campos.forEach(campo => {
                        let valoresHtml = '';
                        campo.valores.forEach(v => {
                            valoresHtml += renderValor(campo.tipo, v);
                        });
                        if (!valoresHtml) valoresHtml = '<span class="text-muted">—</span>';

                        html += `<div class="col-12 col-md-6 col-lg-4">
                            <div class="pb-1 mb-1" style="border-bottom:1px solid #dee2e6;">
                                <div class="text-uppercase small mb-1">${campo.etiqueta}</div>
                                <div class="fw-medium">${valoresHtml}</div>
                            </div>
                         </div>`;
                    });
                    return html;
                };

                fetch(`/formularios/${formId}/respuestas/${respuestaId}/visor`)
                    .then(response => response.json())
                    .then(data => {

                        if (data.error) {
                            contenido.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            return;
                        }

                        // Respuesta principal
                        let html = `<div class="row g-2">`;
                        html += renderCampos(data.campos);
                        html += `</div>`;

                        // Tabla de respuestas asociadas
                        if (data.respuestas_grupo && data.respuestas_grupo.length > 0) {

                            tooltipIcon.classList.remove('d-none');
                            tooltipIcon.classList.add('d-inline');

                            new bootstrap.Tooltip(tooltipIcon, {
                                title: "Este registro fue realizado de forma múltiple. Debajo se muestran todas las respuestas asociadas a este grupo.",
                                placement: "top"
                            });

                            html += `<hr class="my-2"><h6 class="mb-2">Respuestas asociadas a grupo:</h6>`;
                            html += `<div class="table-responsive" style="max-height:300px; overflow:auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-dark">
                                        <tr>`;

                            const encabezados = data.respuestas_grupo[0].campos.map(c => c.etiqueta);
                            encabezados.forEach(enc => html += `<th class="text-center p-1">${enc}</th>`);

                            html += `</tr></thead><tbody>`;

                            data.respuestas_grupo.forEach(resp => {
                                html += `<tr>`;
                                resp.campos.forEach(campo => {
                                    let valor = '';
                                    campo.valores.forEach(v => valor += renderValor(campo.tipo, v));
                                    if (!valor) valor = '—';
                                    html += `<td class="text-center p-1">${valor}</td>`;
                                });
                                html += `</tr>`;
                            });

                            html += `</tbody></table></div>`;
                        }

                        contenido.innerHTML = html;
                    });

                modal.show();
            });
        });
    });
</script>