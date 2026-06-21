@php

    $chartId = 'chart-' . $widget['id'];

    $chartType = match ($widget['tipo']) {
        'WID-007' => 'line',
        'WID-008' => 'bar',
        'WID-009' => 'pie',
        default => 'bar',
    };

    $config = $widget['configuracion'] ?? [];

    $altura = $config['altura'] ?? 200;
    $ancho = $config['ancho'] ?? '100%';

    $colorFondo = $config['color_fondo'] ?? '#ffffff';
    $colorTexto = $config['color_texto'] ?? '#212529';

@endphp




@if (!empty($config['titulo']) || !empty($config['subtitulo']))


    @if (!empty($config['titulo']))
        <h5 class="mb-1">{{ $config['titulo'] }}</h5>
    @endif

    @if (!empty($config['subtitulo']))
        <small>{{ $config['subtitulo'] }}</small>
    @endif


@endif

<div style="height: {{ $altura }}px; width: {{ $ancho }};">
    <canvas id="{{ $chartId }}"></canvas>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        let chartData = @json($widget['data']);

        @if ($chartType === 'pie')

            const baseColor = '{{ $config['color'] ?? '#0d6efd' }}';

            const colores = [];

            chartData.labels.forEach((_, index) => {

                const hue = (index * 45) % 360;

                colores.push(`hsl(${hue},70%,55%)`);

            });

            chartData.datasets[0].backgroundColor = colores;
            chartData.datasets[0].borderColor = '#ffffff';
            chartData.datasets[0].borderWidth = 2;
        @endif

        new Chart(document.getElementById('{{ $chartId }}'), {

            type: '{{ $chartType }}',

            data: chartData,

            options: {

                responsive: true,

                maintainAspectRatio: false,

                animation: {
                    duration: {{ !empty($config['animacion']) ? 1000 : 0 }}
                },

                plugins: {

                    legend: {
                        display: {{ !empty($config['mostrar_leyenda']) ? 'true' : 'false' }},
                        labels: {
                            color: '{{ $colorTexto }}'
                        }
                    },

                    title: {
                        display: false
                    },

                    datalabels: {
                        display: {{ !empty($config['mostrar_etiquetas']) ? 'true' : 'false' }},
                        color: '{{ $colorTexto }}'
                    }

                },

                scales: {

                    @if ($chartType !== 'pie')

                        x: {
                            ticks: {
                                color: '{{ $colorTexto }}'
                            },
                            grid: {
                                display: true
                            }
                        },

                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '{{ $colorTexto }}'
                            },
                            grid: {
                                display: true
                            }
                        }
                    @endif

                }

            },

            plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : []

        });

    });
</script>
