@php
    // Determinar si los permisos ya fueron creados
    $permisosCreados = $formulario->config['crear_permisos'] ?? false;
    $registroMultiple = $formulario->config['registro_multiple'] ?? false;
@endphp


<div class="row g-3">

    <div class="col-12 col-lg-4">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $formulario->nombre ?? '') }}"
            class="form-control @error('nombre') is-invalid @enderror">

        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-lg-4">
        <label for="estado" class="form-label">Estado</label>

        <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror">

            <option value="">Seleccione un estado</option>

            @foreach ($estado_formularios as $estado)
                <option value="{{ $estado->catalogo_codigo }}"
                    {{ old('estado', $formulario?->estado ?? '') == $estado->catalogo_codigo ? 'selected' : '' }}>

                    {{ $estado->catalogo_descripcion }}

                </option>
            @endforeach

        </select>

        @error('estado')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-lg-4">
        <label for="campos_columnas" class="form-label">
            Configuración de columnas
        </label>

        <select name="campos_columnas" id="campos_columnas"
            class="form-select @error('campos_columnas') is-invalid @enderror">

            <option value="">Seleccione una configuración</option>

            @foreach ($conf_formularios as $campos_columnas)
                <option value="{{ $campos_columnas->catalogo_codigo }}"
                    {{ old('campos_columnas', $formulario?->campos_columnas ?? '') == $campos_columnas->catalogo_codigo ? 'selected' : '' }}>

                    {{ $campos_columnas->catalogo_descripcion }}

                </option>
            @endforeach

        </select>

        @error('campos_columnas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>



    <div class="col-12 col-lg-6">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="crear_permisos" id="crear_permisos"
                {{ old('crear_permisos', $permisosCreados) ? 'checked' : '' }}
                {{ $permisosCreados ? 'disabled' : '' }}>

            <label class="form-check-label" for="crear_permisos">
                Crear también los permisos para este formulario
            </label>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="registro_multiple" id="registro_multiple"
                {{ old('registro_multiple', $registroMultiple) ? 'checked' : '' }}>

            <label class="form-check-label" for="registro_multiple">
                Permitir registros múltiples para este formulario
            </label>
        </div>
    </div>


    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción</label>

        <textarea name="descripcion" id="descripcion" rows="4"
            class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $formulario->descripcion ?? '') }}</textarea>

        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
        <a href="{{ route('formularios.index') }}" class="btn btn-secondary">
            Cancelar
        </a>

        <button type="submit" class="btn btn-success">
            Guardar
        </button>
    </div>

</div>
