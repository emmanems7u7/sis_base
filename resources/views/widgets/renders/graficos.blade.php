@php

    $chartId = 'chart-' . $widget['id'];

    $chartType = match ($widget['tipo']) {
        'WID-007' => 'line',
        'WID-008' => 'bar',
        'WID-009' => 'pie',
        default => 'bar',
    };

    $config = $widget['configuracion'] ?? [];

    $altura = $config['altura'] ?? 240;
    $ancho = $config['ancho'] ?? '100%';

    $colorFondo = $config['color_fondo'] ?? '#ffffff';
    $colorTexto = $config['color_texto'] ?? '#212529';
    $color = $config['color'] ?? '#5E72E4';

    // Animación activada por defecto salvo que el config la desactive explícitamente
    //$animar = array_key_exists('animacion', $config) ? !empty($config['animacion']) : true;
    $animar = true;

@endphp


<style>
    .cw-{{ $widget['id'] }} {
        --cw-accent: {{ $color }};
        --cw-ink: {{ $colorTexto }};
        --cw-bg: {{ $colorFondo }};

        width: {{ $ancho }};
        border-radius: 16px;
        border: 1px solid color-mix(in srgb, var(--cw-ink) 8%, transparent);
        background: var(--cw-bg);
        padding: 20px 22px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        transition: box-shadow .25s ease, border-color .25s ease, transform .25s ease;

        opacity: 0;
        transform: translateY(10px);
        animation: cw-enter .5s cubic-bezier(.22, .61, .36, 1) forwards;
    }

    .cw-{{ $widget['id'] }}:hover {
        border-color: color-mix(in srgb, var(--cw-ink) 15%, transparent);
        box-shadow: 0 10px 28px -12px rgba(16, 24, 40, .18);
        transform: translateY(-2px);
    }

    @keyframes cw-enter {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ---- encabezado: punto de color + título ---- */

    .cw-{{ $widget['id'] }} .cw-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 2px;
    }

    .cw-{{ $widget['id'] }} .cw-header-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--cw-accent);
        flex-shrink: 0;
    }

    .cw-{{ $widget['id'] }} .cw-title {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--cw-ink);
        margin: 0;
        letter-spacing: -.01em;
        line-height: 1.3;
    }

    .cw-{{ $widget['id'] }} .cw-subtitle {
        font-size: 12.5px;
        color: color-mix(in srgb, var(--cw-ink) 58%, transparent);
        margin: 4px 0 16px 17px;
        line-height: 1.45;
    }

    .cw-{{ $widget['id'] }} .cw-chart-wrap {
        position: relative;
        width: 100%;
        height: {{ $altura }}px;
        margin-top: 16px;
    }

    .cw-{{ $widget['id'] }} .cw-skeleton {
        position: absolute;
        inset: 0;
        border-radius: 10px;
        background: linear-gradient(100deg,
                color-mix(in srgb, var(--cw-ink) 4%, transparent) 30%,
                color-mix(in srgb, var(--cw-ink) 9%, transparent) 50%,
                color-mix(in srgb, var(--cw-ink) 4%, transparent) 70%);
        background-size: 200% 100%;
        animation: cw-shimmer 1.2s ease-in-out infinite;
        z-index: 0;
    }

    @keyframes cw-shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .cw-{{ $widget['id'] }} .cw-chart-mount {
        position: relative;
        z-index: 1;
        width: 100%;
        height: 100%;
    }

    /* leyenda inferior: SOLO si hay más de una serie/segmento */

    .cw-{{ $widget['id'] }} .cw-legend {
        display: none;
        flex-wrap: wrap;
        gap: 6px 16px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid color-mix(in srgb, var(--cw-ink) 7%, transparent);
    }

    .cw-{{ $widget['id'] }} .cw-legend.is-visible {
        display: flex;
    }

    .cw-{{ $widget['id'] }} .cw-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: color-mix(in srgb, var(--cw-ink) 68%, transparent);
    }

    .cw-{{ $widget['id'] }} .cw-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    @media (prefers-reduced-motion: reduce) {
        .cw-{{ $widget['id'] }} {
            animation: none;
            opacity: 1;
            transform: none;
        }

        .cw-{{ $widget['id'] }} .cw-skeleton {
            animation: none;
        }
    }
</style>


<div class="cw-{{ $widget['id'] }}">

    @if (!empty($config['titulo']))
        <div class="cw-header">
            <span class="cw-header-dot"></span>
            <h5 class="cw-title">{{ $config['titulo'] }}</h5>
        </div>
    @endif

    @if (!empty($config['subtitulo']))
        <p class="cw-subtitle">{{ $config['subtitulo'] }}</p>
    @endif

    <div class="cw-chart-wrap">
        <div class="cw-skeleton" id="skeleton-{{ $chartId }}"></div>
        <div class="cw-chart-mount" id="{{ $chartId }}"></div>
    </div>

    @if (!empty($config['mostrar_leyenda']))
        <div class="cw-legend" id="legend-{{ $chartId }}"></div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts no está cargado.');
            return;
        }

        const mount = document.getElementById('{{ $chartId }}');
        const skeleton = document.getElementById('skeleton-{{ $chartId }}');
        const legendEl = document.getElementById('legend-{{ $chartId }}');

        if (!mount) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DATOS
        |--------------------------------------------------------------------------
        */

        const raw = @json($widget['data']);

        const chartType = '{{ $chartType }}';

        const baseColor = '{{ $color }}';

        const textColor = '{{ $colorTexto }}';

        const backgroundColor = '{{ $colorFondo }}';

        const animar = {{ $animar ? 'true' : 'false' }};


        /*
        |--------------------------------------------------------------------------
        | PALETA
        |--------------------------------------------------------------------------
        */

        const paleta = [
            baseColor,
            '#2DCE89',
            '#11CDEF',
            '#FB6340',
            '#F5365C',
            '#8965E0',
            '#FFA726',
            '#26A69A',
            '#AB47BC',
            '#42A5F5'
        ];


        /*
        |--------------------------------------------------------------------------
        | CONVERTIR HEX A RGBA
        |--------------------------------------------------------------------------
        */

        function hexToRgba(hex, alpha) {

            if (!hex || typeof hex !== 'string') {
                return `rgba(0,0,0,${alpha})`;
            }

            let clean = hex.replace('#', '');

            if (clean.length === 3) {
                clean = clean
                    .split('')
                    .map(c => c + c)
                    .join('');
            }

            const bigint = parseInt(clean, 16);

            const r = (bigint >> 16) & 255;
            const g = (bigint >> 8) & 255;
            const b = bigint & 255;

            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }


        /*
        |--------------------------------------------------------------------------
        | LEYENDA
        |--------------------------------------------------------------------------
        */

        function pintarLeyenda(items) {

            if (!legendEl) {
                return;
            }

            const debeMostrarse =
                chartType === 'pie' ?
                items.length > 0 :
                items.length > 1;

            if (!debeMostrarse) {
                return;
            }

            legendEl.classList.add('is-visible');

            legendEl.innerHTML = items.map(item => `
            <span class="cw-legend-item">

                <span
                    class="cw-legend-dot"
                    style="background:${item.color}">
                </span>

                ${item.label}

            </span>
        `).join('');
        }


        /*
        |--------------------------------------------------------------------------
        | ANIMACIONES
        |--------------------------------------------------------------------------
        */

        const animationOptions = {

            enabled: animar,

            easing: 'easeinout',

            speed: 900,

            animateGradually: {
                enabled: true,
                delay: 120
            },

            dynamicAnimation: {
                enabled: true,
                speed: 700
            }

        };


        /*
        |--------------------------------------------------------------------------
        | LINEA / BARRAS
        |--------------------------------------------------------------------------
        */

        if (chartType === 'line' || chartType === 'bar') {

            const series = (raw.datasets || []).map((ds, i) => ({

                name: ds.label || `Serie ${i + 1}`,

                data: (ds.data || []).map(value => {

                    const numero = Number(value);

                    return Number.isFinite(numero) ?
                        numero :
                        0;

                })

            }));


            /*
            |--------------------------------------------------------------------------
            | COLORES
            |--------------------------------------------------------------------------
            */

            const colores = (raw.datasets || []).map((ds, i) => {

                let color;

                if (chartType === 'line') {
                    color = ds.borderColor;
                } else {
                    color = ds.backgroundColor;
                }

                return typeof color === 'string' ?
                    color :
                    paleta[i % paleta.length];

            });


            /*
            |--------------------------------------------------------------------------
            | VALORES
            |--------------------------------------------------------------------------
            */

            const todosLosValores = series
                .flatMap(s => s.data)
                .filter(v => typeof v === 'number');


            const hayNegativos =
                todosLosValores.some(v => v < 0);


            /*
            |--------------------------------------------------------------------------
            | SERIES INICIALES EN CERO
            |
            | El gráfico comienza aquí.
            | Después hacemos updateSeries() con los valores reales.
            |--------------------------------------------------------------------------
            */

            const seriesEnCero = series.map(s => ({

                name: s.name,

                data: s.data.map(() => 0)

            }));


            /*
            |--------------------------------------------------------------------------
            | OPCIONES
            |--------------------------------------------------------------------------
            */

            const options = {

                chart: {

                    type: chartType,

                    height: '100%',

                    width: '100%',

                    toolbar: {
                        show: false
                    },

                    fontFamily: 'inherit',

                    animations: animationOptions

                },


                /*
                |--------------------------------------------------------------------------
                | IMPORTANTE:
                | Comenzamos con todos los valores en 0
                |--------------------------------------------------------------------------
                */

                series: seriesEnCero,


                colors: colores,


                /*
                |--------------------------------------------------------------------------
                | LINEA
                |--------------------------------------------------------------------------
                */

                stroke: {

                    curve: 'smooth',

                    width: chartType === 'line' ?
                        3 : 0

                },


                /*
                |--------------------------------------------------------------------------
                | AREA DE LA LINEA
                |--------------------------------------------------------------------------
                */

                fill: chartType === 'line' ? {

                    type: 'gradient',

                    gradient: {

                        shadeIntensity: 1,

                        opacityFrom: 0.32,

                        opacityTo: 0.02,

                        stops: [
                            0,
                            95,
                            100
                        ]

                    }

                } : {
                    opacity: 1
                },


                /*
                |--------------------------------------------------------------------------
                | PUNTOS
                |--------------------------------------------------------------------------
                */

                markers: {

                    size: 0,

                    hover: {

                        size: 5

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | BARRAS
                |--------------------------------------------------------------------------
                */

                plotOptions: chartType === 'bar' ? {

                    bar: {

                        borderRadius: 6,

                        columnWidth: '55%',

                        borderRadiusApplication: 'end'

                    }

                } : {},


                /*
                |--------------------------------------------------------------------------
                | ETIQUETAS
                |--------------------------------------------------------------------------
                */

                dataLabels: {

                    enabled: {{ !empty($config['mostrar_etiquetas']) ? 'true' : 'false' }}

                },


                /*
                |--------------------------------------------------------------------------
                | LEYENDA DE APEX
                |--------------------------------------------------------------------------
                */

                legend: {

                    show: false

                },


                /*
                |--------------------------------------------------------------------------
                | GRID
                |--------------------------------------------------------------------------
                */

                grid: {

                    borderColor: 'rgba(16, 24, 40, 0.06)',

                    strokeDashArray: 4,

                    xaxis: {

                        lines: {
                            show: false
                        }

                    },

                    yaxis: {

                        lines: {
                            show: true
                        }

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | EJE X
                |--------------------------------------------------------------------------
                */

                xaxis: {

                    categories: raw.labels || [],

                    labels: {

                        style: {

                            colors: hexToRgba(textColor, 0.6),

                            fontSize: '11px'

                        }

                    },

                    axisBorder: {

                        show: false

                    },

                    axisTicks: {

                        show: false

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | EJE Y
                |--------------------------------------------------------------------------
                */

                yaxis: {

                    min: hayNegativos ?
                        undefined : 0,

                    labels: {

                        style: {

                            colors: hexToRgba(textColor, 0.6),

                            fontSize: '11px'

                        }

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | TOOLTIP
                |--------------------------------------------------------------------------
                */

                tooltip: {

                    theme: 'dark',

                    y: {

                        formatter: function(val) {

                            return new Intl.NumberFormat('es')
                                .format(val);

                        }

                    }

                }

            };


            /*
            |--------------------------------------------------------------------------
            | CREAR GRÁFICO
            |--------------------------------------------------------------------------
            */

            const chartInstance =
                new ApexCharts(mount, options);


            /*
            |--------------------------------------------------------------------------
            | RENDER
            |--------------------------------------------------------------------------
            */

            chartInstance.render().then(() => {

                /*
                |--------------------------------------------------------------------------
                | OCULTAR SKELETON
                |--------------------------------------------------------------------------
                */

                if (skeleton) {

                    skeleton.style.opacity = '0';

                    setTimeout(() => {

                        skeleton.style.display = 'none';

                    }, 250);

                }


                /*
                |--------------------------------------------------------------------------
                | SIN ANIMACIÓN
                |--------------------------------------------------------------------------
                */

                if (!animar) {

                    chartInstance.updateSeries(
                        series,
                        false
                    );

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | ANIMACIÓN 0 → VALOR REAL
                |
                | Aquí ocurre realmente el efecto de crecimiento.
                |--------------------------------------------------------------------------
                */

                setTimeout(() => {

                    chartInstance.updateSeries(
                        series,
                        true
                    );

                }, 100);

            });


            /*
            |--------------------------------------------------------------------------
            | LEYENDA PERSONALIZADA
            |--------------------------------------------------------------------------
            */

            pintarLeyenda(

                series.map((s, i) => ({

                    label: s.name,

                    color: colores[i]

                }))

            );


            return;

        }


        /*
        |--------------------------------------------------------------------------
        | PIE
        |--------------------------------------------------------------------------
        */

        const values =
            (
                raw.datasets &&
                raw.datasets[0] &&
                raw.datasets[0].data
            ) || [];


        const labels =
            raw.labels || [];


        const valoresReales =
            values.map(value => {

                const numero = Number(value);

                return Number.isFinite(numero) ?
                    numero :
                    0;

            });


        const colores = labels.map(
            (_, i) =>
            paleta[i % paleta.length]
        );


        /*
        |--------------------------------------------------------------------------
        | VALORES INICIALES
        |
        | ApexCharts no dibuja correctamente un pie
        | cuando todos los valores son exactamente 0.
        |
        | Por eso usamos un valor mínimo.
        |--------------------------------------------------------------------------
        */

        const valoresIniciales =
            valoresReales.map(() => 0.0001);


        /*
        |--------------------------------------------------------------------------
        | OPCIONES PIE
        |--------------------------------------------------------------------------
        */

        const options = {

            chart: {

                type: 'pie',

                height: '100%',

                width: '100%',

                toolbar: {
                    show: false
                },

                fontFamily: 'inherit',

                animations: animationOptions

            },


            /*
            |--------------------------------------------------------------------------
            | COMIENZA CASI EN 0
            |--------------------------------------------------------------------------
            */

            series: valoresIniciales,


            labels: labels,


            colors: colores,


            stroke: {

                width: 3,

                colors: [
                    backgroundColor
                ]

            },


            legend: {

                show: false

            },


            dataLabels: {

                enabled: {{ !empty($config['mostrar_etiquetas']) ? 'true' : 'false' }},

                style: {

                    fontSize: '11px',

                    fontWeight: 600

                }

            },


            tooltip: {

                theme: 'dark',

                y: {

                    formatter: function(val) {

                        return new Intl.NumberFormat('es')
                            .format(val);

                    }

                }

            }

        };


        /*
        |--------------------------------------------------------------------------
        | CREAR PIE
        |--------------------------------------------------------------------------
        */

        const chartInstance =
            new ApexCharts(mount, options);


        /*
        |--------------------------------------------------------------------------
        | RENDER PIE
        |--------------------------------------------------------------------------
        */

        chartInstance.render().then(() => {

            if (skeleton) {

                skeleton.style.opacity = '0';

                setTimeout(() => {

                    skeleton.style.display = 'none';

                }, 250);

            }


            /*
            |--------------------------------------------------------------------------
            | SIN ANIMACIÓN
            |--------------------------------------------------------------------------
            */

            if (!animar) {

                chartInstance.updateSeries(
                    valoresReales,
                    false
                );

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | ANIMACIÓN
            |--------------------------------------------------------------------------
            */

            setTimeout(() => {

                chartInstance.updateSeries(
                    valoresReales,
                    true
                );

            }, 100);

        });


        /*
        |--------------------------------------------------------------------------
        | LEYENDA
        |--------------------------------------------------------------------------
        */

        pintarLeyenda(

            labels.map((label, i) => ({

                label: label,

                color: colores[i]

            }))

        );

    });
</script>
