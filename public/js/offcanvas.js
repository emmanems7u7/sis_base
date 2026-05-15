
document.addEventListener('DOMContentLoaded', function () {

    const offcanvasEl = document.getElementById('offcanvasAcciones');
    const contenido = document.getElementById('accionesContenido');
    const templateEl = document.getElementById('acciones-template');

    if (!offcanvasEl || !contenido || !templateEl) {
        return;
    }

    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

   
    document.addEventListener('click', function (e) {

        const card = e.target.closest('.respuesta-card');
        if (!card) return;

        if (e.target.closest('a, button, input')) return;

      
        card.classList.add('clicked');

        setTimeout(() => {
            card.classList.remove('clicked');
        }, 150);

        const respuestaId = card.dataset.respuestaId;
        const formId = card.dataset.formId;


        const templateBase = templateEl.cloneNode(true).innerHTML;

        let template = templateBase;

        template = template.replaceAll('__RESPUESTA_ID__', respuestaId);
        template = template.replaceAll('__FORM_ID__', formId);

        contenido.innerHTML = template;

        offcanvas.show();
    });


    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        if (!el._tooltipInit) {
            new bootstrap.Tooltip(el);
            el._tooltipInit = true;
        }
    });

});