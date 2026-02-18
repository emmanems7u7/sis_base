<div class="modal fade" id="modalVerRespuesta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Registro</h5>
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

                const formId = this.dataset.formId;
                const respuestaId = this.dataset.respuestaId;

                const modal = new bootstrap.Modal(document.getElementById('modalVerRespuesta'));
                const contenido = document.getElementById('contenidoRespuesta');





                fetch(`/formularios/${formId}/respuestas/${respuestaId}/visor`)
                    .then(response => response.json())


                    .then(data => {

                        if (data.error) {
                            contenido.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            return;
                        }

                        let html = `
  

    <div class="row g-4">
`;

                        data.campos.forEach(campo => {

                            let valoresHtml = '';

                            campo.valores.forEach(v => {

                                switch (campo.tipo) {

                                    case 'imagen':
                                        valoresHtml += `
                    <img src="${v}" 
                         class="img-fluid rounded mb-2"
                         style="max-height:140px;">
                `;
                                        break;

                                    case 'video':
                                        valoresHtml += `
                    <video src="${v}" 
                           controls 
                           class="w-100 rounded mb-2"
                           style="max-height:160px;">
                    </video>
                `;
                                        break;

                                    case 'archivo':
                                        valoresHtml += `
                    <a href="${v}" target="_blank" 
                       class="d-inline-block mb-2 text-decoration-none">
                       📎 Descargar archivo
                    </a>
                `;
                                        break;

                                    case 'enlace':
                                        valoresHtml += `
                    <a href="${v}" target="_blank" 
                       class="d-block mb-1 text-decoration-none">
                       🔗 ${v}
                    </a>
                `;
                                        break;

                                    case 'color':
                                        valoresHtml += `
                    <div class="d-flex align-items-center mb-1">
                        <span style="width:18px;height:18px;border-radius:4px;background:${v};margin-right:6px;"></span>
                        ${v}
                    </div>
                `;
                                        break;

                                    default:
                                        valoresHtml += `<div class="mb-1">${v ?? '—'}</div>`;
                                }

                            });

                            if (!valoresHtml) {
                                valoresHtml = '<span class="text-muted">—</span>';
                            }

                            html += `
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="pb-2 mb-2" style="border-bottom:1px solid #dee2e6;">
                                        <div class="text-uppercase small text-muted mb-1">
                                            ${campo.etiqueta}
                                        </div>
                                        <div class="fw-medium">
                                            ${valoresHtml}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `</div>`;

                        contenido.innerHTML = html;
                    });

                modal.show();


            });

        });

    });
</script>