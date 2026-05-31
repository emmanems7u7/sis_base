document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (e) {

        if (e.target.closest('.form-check-input, .form-check-label')) {
            return;
        }

        const trigger = e.target.closest('[data-open-offcanvas]');
        if (!trigger) return;

        const offcanvasEl = document.getElementById(trigger.dataset.openOffcanvas);
        if (!offcanvasEl) return;

        const contenido = document.getElementById(offcanvasEl.dataset.content);

        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

        //CLAVE: buscar botones dentro del MISMO CARD
        const card = trigger.closest('.card');
        const source = card.querySelector('.acciones-source');

        if (!source || !contenido) return;

        contenido.innerHTML = source.innerHTML;

        offcanvas.show();

        // tooltips opcional
        contenido.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {

            if (!el._tooltipInit) {
                new bootstrap.Tooltip(el);
                el._tooltipInit = true;
            }
        });

    });

});