@extends('layouts.argon')

@section('content')
<style>
.minimal-card-horizontal {
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.minimal-card-horizontal:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.response-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: #fff;
    font-weight: bold;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.form-name {
    font-size: 0.95rem;
    font-weight: 500;
    color: #212529;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
    @if($tiempo_cambio_contraseña != 1)
    <div class="container mb-3">

    @if(!empty($grid))
    <div class="row g-3">

        @foreach($grid as $fila)
            @foreach($fila['columnas'] as $col)

                <div class="{{ $col['clases'] }}">

                    @php $widget = $col['widget']; @endphp

                    @switch($widget['tipo'])

                        {{-- 🔘 BOTÓN --}}
                        @case('WID-001')
                        @php
                            $cfg = $widget['data']['configuracion'];
                        @endphp

                        <a href="{{ $cfg['url'] ?? '#' }}"
                        class="btn"
                        style="background-color: {{ $cfg['color'] ?? '#0d6efd' }}; color:#fff;">

                            @if(!empty($cfg['icono']))
                                <i class="{{ $cfg['icono'] }} me-1"></i>
                            @endif

                            {{ $cfg['texto'] ?? 'Botón' }}
                        </a>
                    @break


                        {{-- 📊 ESTADÍSTICA --}}
                        @case('WID-002')
                            <div class="card shadow-sm border-0 text-center h-100">
                                <div class="card-body d-flex flex-column justify-content-center">

                                    <div class="text-muted text-uppercase small mb-1">
                                        {{ strtoupper($widget['data']['tipo_estadistica']) }}
                                    </div>

                                    <div class="display-5 fw-bold text-primary">
                                        {{ $widget['data']['resultado'] }}
                                    </div>

                                    <div class="mt-2 small text-muted">
                                        {{ $widget['data']['campo'] }}
                                    </div>

                                    @if($widget['data']['fecha'] === 'anio_actual')
                                        <div class="small text-muted">
                                            Año {{ now()->year }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @break
                        @case('WID-004')
                        <div class="card">
    <div class="card-body">
             {{-- Tabla para pantallas grandes --}}
<h6> Registros en: {{  $widget['data']['resultado']['formulario']->nombre }} </h6>
             @if($widget['data']['isMobile'])
                            @include('formularios.respuestas_movil', [
                                'formulario' => $widget['data']['resultado']['formulario'],
                                'respuestas' => $widget['data']['resultado']['respuestas'],
                                'campos' => $widget['data']['campos']
                                ])
                        @else
                            @include('formularios.respuestas_desktop', [
                                'formulario' => $widget['data']['resultado']['formulario'],
                                'respuestas' => $widget['data']['resultado']['respuestas'],
                                'campos' => $widget['data']['campos']
                            ])
                        @endif

                        {{-- Cards para móviles --}}


                        <div class="d-flex justify-content-center mt-2">
                            {{ $widget['data']['resultado']['respuestas']->links('pagination::bootstrap-4') }}
                        </div>
    </div>
</div>
                    
                        @break

                        {{-- 📄 FORMULARIO --}}
                        @case('WID-006')
                        
                            @include('formularios.partials.form_registrar', [
                                'formulario' => $widget['data']['formulario'],
                                'modulo' => $widget['data']['modulo']
                            ])
                        @break


                        {{-- 🔢 CONTADOR --}}
                        @case('WID-010')
                            <div class="card minimal-card-horizontal">
                                <div class="d-flex align-items-center">
                                    <div class="response-circle">
                                        {{ $widget['data']['contador'] }}
                                    </div>
                                    <div class="form-name ms-3">
                                        {{ $widget['data']['nombre'] }}
                                    </div>
                                </div>
                            </div>
                        @break


                        {{-- ❓ DEFAULT --}}
                        @default
                            <div class="card shadow-sm text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        {{ $widget['data']['nombre'] ?? 'Widget' }}
                                    </h5>
                                    <p class="text-muted">Tipo no soportado</p>
                                </div>
                            </div>

                    @endswitch

                </div>

            @endforeach
        @endforeach

    </div>
@else
    <div class="alert alert-warning">
        No hay widgets configurados para tu rol.
    </div>
@endif

</div>


    @else
        <div class="alert alert-warning" role="alert">
            <strong>!Alerta!</strong> Debes actualizar tu contraseña
        </div>

    @endif
@endsection