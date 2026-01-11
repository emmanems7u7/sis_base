@csrf

<div class="row">
<div class="col-md-6">
        <div class="mb-3">
            <label for="categoria" class="form-label">
                {{ __('lo.categoria') }}:
            </label>
            <input type="hidden" id="categoria_original" value="{{ $catalogo->categoria_id ?? '' }}">
            <input type="hidden" id="codigo_original" value="{{ $catalogo->catalogo_codigo ?? '' }}">

            <select class="form-select" id="categoria" name="categoria" required>
                <option value="" disabled {{ old('categoria', $catalogo->categoria_id ?? '') == '' ? 'selected' : '' }}>
                    -- {{ __('ui.select_one_text') }} {{ __('lo.categoria') }} --
                </option>

                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria', $catalogo->categoria_id ?? '') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }} | {{ $categoria->estado ? 'Activo' : 'Inactivo' }}
                    </option>
                @endforeach
            </select>

            @error('categoria')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="catalogo_codigo" class="form-label">{{ __('ui.code_text') }}</label>
            <input readonly type="text" class="form-control" id="catalogo_codigo" name="catalogo_codigo"
                value="{{ old('catalogo_codigo', $catalogo->catalogo_codigo ?? '') }}" maxlength="50" required>
            @error('catalogo_codigo')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="tipoCampo" class="form-label">¿Depende de otro catálogo?</label>

            <select id="tipoCampo" name="tipo" class="form-select tom-select">
                <option value="" disabled {{ old('tipo', $catalogo->catalogo_parent ?? '') == '' ? 'selected' : '' }}>
                    Seleccione un catálogo
                </option>

                @foreach ($catalogos as $c)
                    <option value="{{ $c->catalogo_codigo }}" {{ old('tipo', $catalogo->catalogo_parent ?? '') == $c->catalogo_codigo ? 'selected' : '' }}>
                        {{ $c->catalogo_descripcion }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="catalogo_descripcion" class="form-label">Contenido</label>
            <input type="text" class="form-control" id="catalogo_descripcion" name="catalogo_descripcion"
                value="{{ old('catalogo_descripcion', $catalogo->catalogo_descripcion ?? '') }}" maxlength="100"
                required>
            @error('catalogo_descripcion')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="catalogo_estado" class="form-label">{{ __('ui.status_text') }}</label>
            <select class="form-select" id="catalogo_estado" name="catalogo_estado">
                <option value="1" {{ old('catalogo_estado', $catalogo->catalogo_estado ?? 1) == 1 ? 'selected' : '' }}>
                    Activo</option>
                <option value="0" {{ old('catalogo_estado', $catalogo->catalogo_estado ?? 1) == 0 ? 'selected' : '' }}>
                    Inactivo</option>
            </select>
            @error('catalogo_estado')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="text-end">
        <a href="{{ route('catalogos.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>

        <button type="submit" class="btn btn-sm btn-primary">
            {!! __('ui.save') !!}
        </button>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoriaSelect = document.getElementById('categoria');
        const codigoInput = document.getElementById('catalogo_codigo');

        const categoriaOriginal = document.getElementById('categoria_original')?.value;
        const codigoOriginal = document.getElementById('codigo_original')?.value;

        categoriaSelect.addEventListener('change', function () {
            const categoriaSeleccionada = this.value;

            // 👉 Caso EDIT: vuelve a la categoría original
            if (categoriaOriginal && categoriaSeleccionada == categoriaOriginal) {
                codigoInput.value = codigoOriginal;
                return;
            }

            // 👉 Consulta backend para nuevo código
            fetch(`/catalogos/ultimo-codigo/${categoriaSeleccionada}`)
                .then(response => response.json())
                .then(data => {
                    if (data.codigo) {
                        codigoInput.value = generarSiguienteCodigo(data.codigo);
                    }
                })
                .catch(() => {
                    codigoInput.value = '';
                });
        });
        function generarSiguienteCodigo(codigo) {
            const [prefijo, numero] = codigo.split('-');
            const siguiente = parseInt(numero, 10) + 1;

            return `${prefijo}-${siguiente.toString().padStart(3, '0')}`;
        }
    });
</script>