<div id="formulario-dinamico">
    <div class="row g-4">
        @foreach ($campos as $campo)
            @php
                $tipo = $campo->tipo;

                $tiposSinLayout = ['CAMPF-027', 'CAMPF-030', 'CAMPF-031'];
                $esHidden = in_array($tipo, $tiposSinLayout);

                $cols = $cols ?? 2;

                if (!empty($campo->config['columna'])) {
                    $colClass = $campo->config['columna'];
                } elseif ($tipo === 'CAMPF-014') {
                    $colClass = 'col-12';
                } else {
                    $grid = $formulario->grid;

                    $colClass = $grid;
                }

                $esRequerido = false;
                if (isset($formulario->config['registro_multiple']) && !$formulario->config['registro_multiple']) {
                    $esRequerido = isset($requerido) ? (bool) $requerido : (bool) $campo->requerido;
                }

                $mostrarAsterisco = isset($requerido) ? (bool) $requerido : (bool) $campo->requerido;

                $valoresCampo = $valores[$campo->id] ?? [];
                if (isset($formulario->config['registro_multiple']) && $formulario->config['registro_multiple']) {
                    $valor = '';
                } else {
                    $valor = old($campo->id, $valoresCampo[0] ?? '');
                }
                $prefix = $prefix ?? '';
                $inputName = $prefix ? "{$prefix}[{$campo->id}]" : $campo->id;
                $etiqueta = $campo->etiqueta;
                $inputId = $prefix ? "{$prefix}_{$campo->id}" : $campo->id;
            @endphp

            @if (!$esHidden)
                <div class="{{ $colClass }}">
            @endif
            @if (!$esHidden)
                <div class="d-flex align-items-center gap-1 mb-1">
                    <label class="form-label fw-bold mb-0">
                        {{ $campo->etiqueta }}
                        @if ($mostrarAsterisco)
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    @if ($tipo == 'CAMPF-017')
                        <i class="fas fa-search text-secondary btn-buscar-opcion" style="cursor: pointer;"
                            data-bs-toggle="modal" data-bs-target="#modalBusqueda" data-campo-id="{{ $campo->id }}"
                            title="Buscar"></i>
                    @endif
                </div>
            @endif
            @switch($tipo)
                {{-- INPUTS BÁSICOS --}}
                @case('CAMPF-012')
                    {{-- text --}}
                @case('CAMPF-025')
                    {{-- email --}}
                @case('CAMPF-013')
                    {{-- number --}}
                @case('CAMPF-026')
                    {{-- password --}}
                @case('CAMPF-020')
                    {{-- enlace  --}}
                    @include('formularios.componentes.input_basico')
                @break

                {{-- TEXTAREA --}}
                @case('CAMPF-014')
                    @include('formularios.componentes.textarea')
                @break

                {{-- FECHA --}}
                @case('CAMPF-021')
                    @include('formularios.componentes.fecha')
                @break

                {{-- HORA --}}
                @case('CAMPF-022')
                    @include('formularios.componentes.hora')
                @break

                {{-- SELECTOR --}}
                @case('CAMPF-017')
                    @include('formularios.componentes.selector')
                @break

                {{-- CHECKBOX --}}
                @case('CAMPF-015')
                    @include('formularios.componentes.checkbox')
                @break

                {{-- RADIO --}}
                @case('CAMPF-016')
                    @include('formularios.componentes.radio')
                @break

                {{-- ARCHIVOS --}}
                @case('CAMPF-023')
                    @include('formularios.componentes.archivo')
                @break

                {{-- IMAGEN --}}
                @case('CAMPF-018')
                    @include('formularios.componentes.imagen')
                @break

                {{-- VIDEO --}}
                @case('CAMPF-019')
                    @include('formularios.componentes.video')
                @break

                {{-- COLOR --}}
                @case('CAMPF-024')
                    @include('formularios.componentes.color')
                @break

                {{-- AUTOCOMPLETADO (HIDDEN) --}}
                @case('CAMPF-027')
                    @include('formularios.componentes.autocompletado')
                @break

                {{-- CAMPO RELACION --}}
                @case('CAMPF-028')
                    @include('formularios.componentes.relacion')
                @break

                {{-- CAMPO IDENTIFICADOR --}}
                @case('CAMPF-032')
                    @include('formularios.componentes.identificador')
                @break

                {{-- CAMPO HIDDEN --}}
                @case('CAMPF-030')
                    @include('formularios.componentes.hidden')
                @break

                {{-- ASOCIADO --}}
                @case('CAMPF-031')
                    @include('formularios.componentes.hidden')
                @break
            @endswitch

            @if (!$esHidden)
    </div>
    @endif
    @endforeach


</div>
</div>




@include('formularios.campos.modal_busqueda')
