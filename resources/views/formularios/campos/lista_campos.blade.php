<label>
    {{ $campo->etiqueta }}
    @if($campo->requerido)
        <span class="text-danger">*</span>
    @endif
</label>

@switch(strtolower($campo->campo_nombre))
    @case('text')
        <input type="text" name="{{ $campo->nombre }}" class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('number')
        <input type="number" name="{{ $campo->nombre }}" class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('textarea')
        <textarea name="{{ $campo->nombre }}" class="form-control" 
                {{ $campo->requerido ? 'required' : '' }}
                placeholder="{{ $campo->config['placeholder'] ?? '' }}"></textarea>
        @break

    @case('checkbox')
        <div class="opciones-container" data-campo-id="{{ $campo->id }}">
        @foreach($campo->opciones_catalogo as $opcion)
            <div class="form-check">
                <input type="checkbox" 
                    name="{{ $campo->nombre }}[]" 
                    value="{{ $opcion->catalogo_codigo }}" 
                    class="form-check-input"
                    id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                <label class="form-check-label" for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    {{ $opcion->catalogo_descripcion }}
                </label>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-primary mt-2 btn-ver-mas-checkbox" data-campo-id="{{ $campo->id }}">
        Ver más...
    </button>
        @break

    @case('radio')
    <div class="radio-container" data-campo-id="{{ $campo->id }}">
        @foreach($campo->opciones_catalogo as $opcion)
            <div class="form-check">
                <input type="radio" 
                    name="{{ $campo->nombre }}" 
                    value="{{ $opcion->catalogo_codigo }}" 
                    class="form-check-input"
                    id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                <label class="form-check-label" for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    {{ $opcion->catalogo_descripcion }}
                </label>
            </div>
        @endforeach

        <button type="button" class="btn btn-sm btn-primary btn-ver-mas mt-2">
            Ver más...
        </button>
    </div>
        @break

    @case('selector')
   

<div class="d-flex align-items-center gap-2">
    <select name="{{ $campo->nombre }}" 
            class="form-select tom-select campo-dinamico" 
            data-campo-id="{{ $campo->id }}">
        <option value="">Seleccione...</option>
        @foreach($campo->opciones_catalogo as $opcion)
            <option value="{{ $opcion->catalogo_codigo }}">
                {{ $opcion->catalogo_descripcion }}
            </option>
        @endforeach
    </select>


    <!-- Botón de lupa para abrir modal -->
    <button type="button" class="btn btn-outline-secondary btn-sm btn-buscar-opcion" 
            data-bs-toggle="modal" 
            data-bs-target="#modalBuscarOpcion"
            data-campo-id="{{ $campo->id }}">
        <i class="fas fa-search"></i>
    </button>
</div>
        @break

    @case('imagen')
        <input type="file" 
            name="{{ $campo->nombre }}" 
            accept="image/*" 
            class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('video')
        <input type="file" 
            name="{{ $campo->nombre }}" 
            accept="video/*" 
            class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('enlace')
        <input type="url" 
            name="{{ $campo->nombre }}" 
            class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="https://...">
        @break

    @case('fecha')
        <input type="date" 
            name="{{ $campo->nombre }}" 
            class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('hora')
        <input type="time" 
            name="{{ $campo->nombre }}" 
            class="form-control" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break
        @case('archivo')
    <input type="file" name="{{ $campo->nombre }}" class="form-control"
        {{ $campo->requerido ? 'required' : '' }}>
@break

@case('color')
    <input type="color" name="{{ $campo->nombre }}" class="form-control form-control-color"
        {{ $campo->requerido ? 'required' : '' }}>
@break

@case('email')
    <input type="email" name="{{ $campo->nombre }}" class="form-control"
        {{ $campo->requerido ? 'required' : '' }}
        placeholder="{{ $campo->config['placeholder'] ?? '' }}">
@break

@case('password')
    <input type="password" name="{{ $campo->nombre }}" class="form-control"
        {{ $campo->requerido ? 'required' : '' }}
        placeholder="{{ $campo->config['placeholder'] ?? '' }}">
@break


    @default
        <input type="text" name="{{ $campo->nombre }}" class="form-control">
@endswitch

