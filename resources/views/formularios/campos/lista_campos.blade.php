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
        @break

    @case('radio')
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
        @break

    @case('selector')
        <select name="{{ $campo->nombre }}" class="form-select" {{ $campo->requerido ? 'required' : '' }}>
            <option value="">Seleccione una opción</option>
            @foreach($campo->opciones_catalogo as $opcion)
                <option value="{{ $opcion->catalogo_codigo }}">
                    {{ $opcion->catalogo_descripcion }}
                </option>
            @endforeach
        </select>
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
