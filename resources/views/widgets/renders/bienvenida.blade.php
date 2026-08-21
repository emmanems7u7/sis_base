<div class="card widget-welcome border-0 shadow-sm h-100 overflow-hidden position-relative wc-animate"
    style="
        border-radius: 14px;
        background: #ffffff;
        transition: transform .2s ease, box-shadow .2s ease;
    "
    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 .4rem 1rem rgba(0,0,0,.07)';"
    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">

    <div class="position-absolute top-0 start-0 w-100 wc-bar-top" style="height: 3px; background: #4f46e5;"></div>

    {{-- Reloj en tiempo real --}}
    <div class="position-absolute d-flex align-items-center wc-clock"
        style="
            top: 14px;
            right: 16px;
            font-size: 12.5px;
            font-weight: 600;
            color: #4f46e5;
            background: #4f46e50d;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: .3px;
            gap: 5px;
        ">
        <i class="far fa-clock" style="font-size: 11px;"></i>
        <span id="wc-hora-actual">--:--</span>
    </div>

    <div class="card-body px-3 py-3">

        <div class="d-flex align-items-center" style="gap: 12px;">

            {{-- Icono de saludo animado --}}
            <div class="d-flex align-items-center justify-content-center rounded-circle wc-icon wc-avatar-pulse"
                style="
                    width: 48px;
                    height: 48px;
                    flex-shrink: 0;
                    background: linear-gradient(135deg, #4f46e5, #6366f1);
                    color: #fff;
                    font-size: 1.4rem;
                ">
                <i class="fas fa-hand-paper wc-wave-hand"></i>
            </div>

            <div class="wc-title" style="min-width: 0;">
                <div class="text-muted fw-medium" style="font-size: 11px; letter-spacing: .4px;">
                    Bienvenido de nuevo
                </div>
                <div class="fw-bold text-truncate" style="font-size: 1.05rem; color: #1e293b;">
                    {{ Auth::user()->usuario_nombres }} {{ Auth::user()->usuario_app }} {{ Auth::user()->usuario_apm }}
                </div>
            </div>

        </div>

        {{-- Detalle secundario: fecha --}}
        <div class="d-flex align-items-center mt-2 wc-desc" style="font-size: 11.5px; color: #64748b; gap: 6px;">
            <i class="fas fa-calendar-alt"></i>
            <span id="wc-fecha-actual"></span>
        </div>

        <div class="mt-3 rounded-pill wc-progress-track" style="height: 3px; background: #4f46e515; overflow: hidden;">
            <div class="h-100 rounded-pill wc-progress-fill"
                style="background: #4f46e5; width: 0%; transition: width 1s ease .3s;"></div>
        </div>

    </div>

</div>


<script>
    document.querySelectorAll('.widget-welcome').forEach(function(card) {
        if (card.dataset.wcInit) return;
        card.dataset.wcInit = "1";

        // Fecha legible en español
        var fechaEl = card.querySelector('#wc-fecha-actual');
        if (fechaEl) {
            var hoy = new Date();
            var opciones = {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            };
            var texto = hoy.toLocaleDateString('es-ES', opciones);
            fechaEl.textContent = texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        // Reloj en tiempo real (hora:minuto), actualizado cada segundo
        var horaEl = card.querySelector('#wc-hora-actual');
        if (horaEl) {
            function actualizarHora() {
                var ahora = new Date();
                var horas = String(ahora.getHours()).padStart(2, '0');
                var minutos = String(ahora.getMinutes()).padStart(2, '0');
                horaEl.textContent = horas + ':' + minutos;
            }
            actualizarHora();
            setInterval(actualizarHora, 1000);
        }

        // Barra decorativa
        var progressEl = card.querySelector('.wc-progress-fill');
        requestAnimationFrame(function() {
            progressEl.style.width = '100%';
        });
    });
</script>
