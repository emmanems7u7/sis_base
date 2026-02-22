<div class="mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $formulario->nombre ?? '') }}"
        class="form-control @error('nombre') is-invalid @enderror">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción</label>
    <textarea name="descripcion" id="descripcion"
        class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $formulario->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="estado" class="form-label">Estado</label>
    <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror">
        <option value="" selected>Seleccione un estado</option>
        @foreach ($estado_formularios as $estado)
            <option value="{{ $estado->catalogo_codigo }}" {{ old('estado', $formulario?->estado ?? '') == $estado->catalogo_codigo ? 'selected' : '' }}>
                {{ $estado->catalogo_descripcion }}
            </option>
        @endforeach
    </select>
    @error('estado')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


@php
    // Determinar si los permisos ya fueron creados
    $permisosCreados = $formulario->config['crear_permisos'] ?? false;
    $registroMultiple = $formulario->config['registro_multiple'] ?? false;
@endphp

<div class="form-check mb-3">
    <input 
        class="form-check-input" 
        type="checkbox" 
        name="crear_permisos" 
        id="crear_permisos" 
        {{ old('crear_permisos', $permisosCreados) ? 'checked' : '' }}
        {{ $permisosCreados ? 'disabled' : '' }} 
    >
    <label class="form-check-label" for="crear_permisos">
        Crear también los permisos para este formulario
    </label>
</div>

<div class="form-check mb-3">
    <input 
        class="form-check-input" 
        type="checkbox" 
        name="registro_multiple" 
        id="registro_multiple" 
        {{ old('registro_multiple', $registroMultiple) ? 'checked' : '' }}
    >
    <label class="form-check-label" for="registro_multiple">
        Permitir registros múltiples para este formulario
    </label>
</div>
<button type="submit" class="btn btn-success">Guardar</button>
<a href="{{ route('formularios.index') }}" class="btn btn-secondary">Cancelar</a>