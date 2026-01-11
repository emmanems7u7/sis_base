@csrf
@if(isset($categoria))
    @method('PUT')
@endif
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="nombre" class="form-label">{{ __('ui.name_text') }}</label>
            <input type="text" class="form-control" name="nombre" id="nombre"
                value="{{ old('nombre', $categoria->nombre ?? '') }}" required>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="estado" class="form-label">{{ __('ui.status_text') }}</label>
            <select class="form-select" name="estado" id="estado">
                <option value="1" {{ old('estado', $categoria->estado ?? 1) == 1 ? 'selected' : '' }}>{{ __('ui.active_text') }}
                </option>
                <option value="0" {{ old('estado', $categoria->estado ?? 1) == 0 ? 'selected' : '' }}>{{ __('ui.inactive_text') }}
                </option>
            </select>
        </div>
    </div>

    <div class="col-md-">
        <div class="mb-3">
        <label for="descripcion" class="form-label">{{ __('ui.description_text') }}</label>
        <textarea class="form-control" name="descripcion"
            id="descripcion">{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
        </div>
    </div>

</div> 



<div class="text-end">
    <a href="{{ route('catalogos.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
    <button type="submit" class="btn btn-sm  btn-primary">
        {!! __('ui.save') !!}
    </button>
</div>