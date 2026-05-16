@php
    $dark = auth()->user()->preferences &&
        auth()->user()->preferences->dark_mode;
@endphp

<div class="offcanvas offcanvas-bottom border-0 rounded-top-4
    {{ $dark ? 'bg-dark text-white' : 'bg-white text-dark' }}" tabindex="-1" id="{{ $id }}" data-offcanvas
    data-template="{{ $templateId }}" data-content="{{ $contenidoId }}" style="height: {{ $height }};">

    <div class="offcanvas-header justify-content-center position-relative py-2">

        <div class="text-center w-100">
            <div class="fw-semibold small">
                <i class="{{ $icono }} me-1"></i>
                {{ $titulo }}
            </div>

            <div style="width:100%;height:2px;background:#dee2e6;margin:6px auto 0;border-radius:10px;">
            </div>
        </div>
    </div>

    <div class="offcanvas-body d-flex justify-content-center align-items-center gap-2 py-2" id="{{ $contenidoId }}">

        {{ $slot }}

    </div>
</div>