@php
    $cfg = $widget['data']['configuracion'];
    $color = $cfg['color'] ?? '#0d6efd';
@endphp

<a href="{{ $cfg['url'] ?? '#' }}" class="widget-action-btn" style="--btn-color: {{ $color }};">

    @if (!empty($cfg['icono']))
        <span class="widget-action-icon">
            <i class="{{ $cfg['icono'] }}"></i>
        </span>
    @endif

    <span class="widget-action-text">{{ $cfg['texto'] ?? 'Botón' }}</span>
</a>
