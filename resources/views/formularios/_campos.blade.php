

<div class="row g-4">
@foreach($campos as $campo)
@php
    /* ===============================
       COLUMNAS
    =============================== */
    $cols = $cols ?? 2;
    $colSize = intval(12 / max(1, min(12, $cols)));

    if (strtolower($campo->campo_nombre) === 'textarea') {
        $colClass = 'col-12';
    } else {
        $colClass = "col-md-{$colSize}";
    }

    /* ===============================
       REQUERIDO (sobrescribible)
    =============================== */
    // si se envía por include → manda
    // si no → usa el valor del campo
    $esRequerido = isset($requerido)
        ? (bool) $requerido
        : (bool) $campo->requerido;

    /* ===============================
       VALOR
    =============================== */
    $valoresCampo = $valores[$campo->nombre] ?? [];
    $valor = old($campo->nombre, $valoresCampo[0] ?? '');
@endphp


    <div class="{{ $colClass }}">
        <label class="form-label fw-bold">
            {{ $campo->etiqueta }}
            @if($esRequerido)
                <span class="text-danger">*</span>
            @endif
        </label>

        @switch(strtolower($campo->campo_nombre))

            {{-- TEXTO --}}
            @case('text')
                <input type="text" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- NUMBER --}}
            @case('number')
                <input type="number" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- TEXTAREA --}}
            @case('textarea')
                <textarea name="{{ $campo->nombre }}"
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>{{ $valor }}</textarea>
            @break

            {{-- CHECKBOX --}}
            @case('checkbox')
                @php
                    $checkedValues = (array) old($campo->nombre, $valoresCampo);
                @endphp

                <div class="opciones-container" data-campo-id="{{ $campo->id }}">
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="checkbox"
                                name="{{ $campo->nombre }}[]"
                                value="{{ $opcion->catalogo_codigo }}"
                                class="form-check-input campo-formulario"
                                id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                {{ in_array($opcion->catalogo_codigo, $checkedValues) ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="button"
                    class="btn btn-sm btn-primary mt-2 btn-ver-mas-checkbox"
                    data-campo-id="{{ $campo->id }}">
                    Ver más...
                </button>
            @break

            {{-- RADIO --}}
            @case('radio')
                <div class="radio-container" data-campo-id="{{ $campo->id }}">
                    @foreach($campo->opciones_catalogo as $opcion)
                        <div class="form-check">
                            <input type="radio"
                                name="{{ $campo->nombre }}"
                                value="{{ $opcion->catalogo_codigo }}"
                                class="form-check-input campo-formulario"
                                id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}"
                                {{ $valor == $opcion->catalogo_codigo ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                {{ $opcion->catalogo_descripcion }}
                            </label>
                        </div>
                    @endforeach

                    <button type="button" class="btn btn-sm btn-primary btn-ver-mas mt-2">
                        Ver más...
                    </button>
                </div>
            @break

            {{-- SELECTOR --}}
            @case('selector')
                <div class="d-flex align-items-center gap-2">
                    <select name="{{ $campo->nombre }}"
                        class="form-select tom-select campo-dinamico"
                        data-campo-id="{{ $campo->id }}"
                       {{ $esRequerido ? 'required' : '' }}>
                        <option value="">Seleccione...</option>
                        @foreach($campo->opciones_catalogo as $opcion)
                            <option value="{{ $opcion->catalogo_codigo }}"
                                {{ $valor == $opcion->catalogo_codigo ? 'selected' : '' }}>
                                {{ $opcion->catalogo_descripcion }}
                            </option>
                        @endforeach
                    </select>

                    <button type="button"
                        class="btn btn-outline-secondary btn-sm btn-buscar-opcion"
                        data-bs-toggle="modal"
                        data-bs-target="#modalBuscarOpcion"
                        data-campo-id="{{ $campo->id }}">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            @break

            {{-- EMAIL --}}
            @case('email')
                <input type="email" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- PASSWORD --}}
            @case('password')
                <input type="password" name="{{ $campo->nombre }}"
                    class="form-control"
                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- FECHA --}}
            @case('fecha')
                <input type="date" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('Y-m-d') : '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- HORA --}}
            @case('hora')
                <input type="time" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor ? \Carbon\Carbon::parse($valor)->format('H:i') : '' }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- ARCHIVO --}}
            @case('archivo')

            @if($valor)
                <div class="mb-2">
                    <a href="{{ asset('archivos/formulario_'.$form.'/archivos/'.$valor) }}"
                    target="_blank"
                    class="btn btn-outline-primary btn-sm">
                        Ver archivo actual
                    </a>
                </div>
            @endif

            <input type="file"
                name="{{ $campo->nombre }}"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>
            @break

            {{-- IMAGEN --}}
            @case('imagen')

            @if($valor)
                <div class="mb-2">
                    <img src="{{ asset('archivos/formulario_'.$form.'/imagenes/'.$valor) }}"
                        class="img-thumbnail"
                        style="max-height: 150px">
                </div>
            @endif

            <input type="file"
                name="{{ $campo->nombre }}"
                accept="image/*"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>
            @break

            {{-- VIDEO --}}
            @case('video')

            @if($valor)
                <div class="mb-2">
                    <video controls style="max-width: 100%; max-height: 200px">
                        <source src="{{ asset('archivos/formulario_'.$form.'/videos/'.$valor) }}">
                        Tu navegador no soporta video.
                    </video>
                </div>
            @endif

            <input type="file"
                name="{{ $campo->nombre }}"
                accept="video/*"
                class="form-control"
                {{ $esRequerido && !$valor ? 'required' : '' }}>
            @break


            {{-- ENLACE --}}
            @case('enlace')
                <input type="url" name="{{ $campo->nombre }}"
                    class="form-control"
                    value="{{ $valor }}"
                    placeholder="https://..."
                   {{ $esRequerido ? 'required' : '' }}>
            @break

            {{-- COLOR --}}
            @case('color')
                <input type="color" name="{{ $campo->nombre }}"
                    class="form-control form-control-color"
                    value="{{ $valor }}"
                   {{ $esRequerido ? 'required' : '' }}>
            @break

        @endswitch
    </div>
@endforeach

</div>

@include('formularios.campos.modal_busqueda')



