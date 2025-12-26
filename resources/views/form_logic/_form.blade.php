@php
    $isEdit = isset($rule);
@endphp


{{-- Sección principal --}}
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Nombre de la Regla</label>
            <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $rule->nombre ?? '') }}"
                required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Formulario de origen </label>
            <select class="form-select select-formulario" id="formulario_id" name="formulario_id" required>
                <option value="">Seleccione...</option>

                @foreach ($formularios as $form)
                    <option value="{{ $form->id }}" {{ (old('formulario_id', $rule->formulario_id ?? '') == $form->id) ? 'selected' : '' }}>
                        {{ $form->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">Evento</label>
            <select name="evento" class="form-select tipo_valor_principal" required>
                @php
                    $eventos = ['on_create' => 'Al Crear', 'on_update' => 'Al Actualizar', 'on_delete' => 'Al Eliminar'];
                @endphp
                @foreach($eventos as $key => $label)
                    <option value="{{ $key }}" {{ (old('evento', $rule->evento ?? '') == $key) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ old('activo', $rule->activo ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="activo">Activo</label>
</div>