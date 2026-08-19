<div class="card widget-counter border-0 shadow-sm h-100 overflow-hidden position-relative wc-animate"
    style="border-radius: 14px; transition: transform .2s ease, box-shadow .2s ease;"
    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 .4rem 1rem rgba(0,0,0,.07)';"
    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">

    <div class="position-absolute top-0 start-0 w-100 wc-bar-top"
        style="height: 3px; background: {{ $widget['data']['color'] }};"></div>

    <div class="card-body px-3 py-3">

        <div class="d-flex justify-content-between align-items-center mb-2">

            <div class="d-flex align-items-center" style="gap: 10px;">

                @if ($widget['data']['mostrar_icono'])
                    <div class="d-flex align-items-center justify-content-center rounded-3 wc-icon"
                        style="
                            width: 36px;
                            height: 36px;
                            flex-shrink: 0;
                            background: linear-gradient(135deg, {{ $widget['data']['color'] }}22, {{ $widget['data']['color'] }}0d);
                            color: {{ $widget['data']['color'] }};
                            font-size: 1rem;
                        ">
                        <i class="{{ $widget['data']['icono'] }}"></i>
                    </div>
                @endif

                <span class="text-uppercase text-muted fw-bold wc-title"
                    style="font-size: 10.5px; letter-spacing: .6px;">
                    {{ $widget['data']['titulo'] }}
                </span>

            </div>

            <span class="d-inline-flex position-relative wc-dot" style="width: 8px; height: 8px;">
                <span class="position-absolute rounded-circle"
                    style="width: 8px; height: 8px; background: {{ $widget['data']['color'] }};"></span>
                <span class="position-absolute rounded-circle"
                    style="width: 8px; height: 8px; background: {{ $widget['data']['color'] }}; opacity:.5; animation: pulse-ring 2s ease-out infinite;"></span>
            </span>

        </div>

        <div class="d-flex align-items-baseline mb-1 wc-counter">

            @if (!empty($widget['data']['prefijo']))
                <span class="text-muted fw-medium me-1" style="font-size: 1rem;">
                    {{ $widget['data']['prefijo'] }}
                </span>
            @endif

            <span class="fw-bold" data-count-to="{{ $widget['data']['contador'] }}"
                style="font-size: 1.75rem; line-height: 1; color: #1e293b; letter-spacing: -0.4px;">
                0
            </span>

            @if (!empty($widget['data']['sufijo']))
                <span class="text-muted fw-medium ms-1" style="font-size: 1rem;">
                    {{ $widget['data']['sufijo'] }}
                </span>
            @endif

        </div>

        @if ($widget['data']['mostrar_descripcion'] && !empty($widget['data']['descripcion']))
            <p class="text-muted mb-0 wc-desc" style="font-size: 11.5px; line-height: 1.3;">
                {{ $widget['data']['descripcion'] }}
            </p>
        @endif

        <div class="mt-2 rounded-pill wc-progress-track"
            style="height: 4px; background: {{ $widget['data']['color'] }}15; overflow: hidden;">
            <div class="h-100 rounded-pill wc-progress-fill" style="background: {{ $widget['data']['color'] }};"></div>
        </div>

    </div>

</div>


<script>
    (function() {
        // Ejecuta el conteo y la barra solo dentro de esta instancia del widget
        document.querySelectorAll('.wc-animate').forEach(function(card) {
            if (card.dataset.wcInit) return; // evita doble ejecución si Blade se re-renderiza
            card.dataset.wcInit = "1";

            var counterEl = card.querySelector('[data-count-to]');
            var progressEl = card.querySelector('.wc-progress-fill');
            var target = parseInt(counterEl.dataset.countTo, 10) || 0;

            // Dispara la barra de progreso
            requestAnimationFrame(function() {
                progressEl.style.width = '65%'; // ver nota abajo sobre valor dinámico
            });

            // Animación de conteo
            var duration = 2000;
            var startTime = null;

            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                var current = Math.floor(eased * target);
                counterEl.textContent = current.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    counterEl.textContent = target.toLocaleString();
                }
            }

            requestAnimationFrame(step);
        });
    })();
</script>
