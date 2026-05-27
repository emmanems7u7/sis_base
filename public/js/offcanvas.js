document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-offcanvas]').forEach(offcanvasEl => {

        const templateId = offcanvasEl.dataset.template;
        const contentId = offcanvasEl.dataset.content;

        const contenido = document.getElementById(contentId);
        const templateEl = document.getElementById(templateId);

        if (!contenido || !templateEl) return;

        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

        document.addEventListener('click', function (e) {

  // evitar abrir si se hace click en checkbox o label
  if (
    e.target.closest('.form-check-input') ||
    e.target.closest('.form-check-label')
) {
    return;
}

            const trigger = e.target.closest('[data-open-offcanvas]');

            if (!trigger) return;

            // validar si este trigger pertenece a este offcanvas
            if (trigger.dataset.openOffcanvas !== offcanvasEl.id) return;

            let template = templateEl.cloneNode(true).innerHTML;

            // =========================
            // placeholders dinámicos
            // =========================

            Object.keys(trigger.dataset).forEach(key => {

                if (key === 'openOffcanvas')
                    return;

                const value = trigger.dataset[key];

                const placeholder = `__${key.toUpperCase()}__`;

                template = template.replaceAll(placeholder, value);

            });

            contenido.innerHTML = template;

            offcanvas.show();

            contenido.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {

                if (!el._tooltipInit) {

                    new bootstrap.Tooltip(el);

                    el._tooltipInit = true;
                }
            });

        });

    });

});