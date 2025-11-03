@php
    $actionIndex = isset($index) ? (int) $index : 0;
@endphp

<div class="accion-block border p-3 mb-3 rounded">
    <div class="d-flex justify-content-between align-items-center">
        <strong>Acción #{{ $actionIndex + 1 }}</strong>
        <button type="button" class="btn btn-sm btn-danger remove-accion">Eliminar Acción</button>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Formulario Destino</label>
            <select name="actions[{{ $actionIndex }}][form_ref_id]" class="form-select" required>
                <option value="">-- Seleccionar Formulario --</option>
                @foreach($formularios as $form)
                    <option value="{{ $form->id }}" {{ (isset($action) && $action->form_ref_id == $form->id) ? 'selected' : '' }}>
                        {{ $form->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label>Campo a Modificar</label>
            <select name="actions[{{ $actionIndex }}][campo_ref_id]" class="form-select">
                <option value="">-- Ninguno --</option>
                @if(isset($action) && $action->formulario)
                    @foreach($action->formulario->campos ?? [] as $campo)
                        <option value="{{ $campo->id }}" {{ ($action->campo_ref_id == $campo->id) ? 'selected' : '' }}>
                            {{ $campo->nombre }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="col-md-6 mt-2">
            <label>Operación</label>
            <select name="actions[{{ $actionIndex }}][operacion]" class="form-select" required>
                @php $ops = ['sumar', 'restar', 'actualizar', 'copiar', 'asignar']; @endphp
                @foreach($ops as $op)
                    <option value="{{ $op }}" {{ (isset($action) && $action->operacion == $op) ? 'selected' : '' }}>
                        {{ ucfirst($op) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mt-2">
            <label>Valor</label>
            <input type="text" name="actions[{{ $actionIndex }}][valor]" value="{{ $action->valor ?? '' }}" class="form-control">
        </div>
    </div>

    <hr>
    <div class="mt-2">
        <h6>
            Condiciones
            <button type="button" class="btn btn-sm btn-primary add-condicion">+ Agregar</button>
        </h6>
        <div class="condiciones-container" data-index="{{ $actionIndex }}">
            @if(isset($action) && $action->conditions->count())
                @foreach($action->conditions as $cond)
                    <div class="condicion-block mb-2">
                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" name="actions[{{ $actionIndex }}][conditions][][campo_condicion]"
                                value="{{ $cond->campo_condicion }}" placeholder="Campo Condición" class="form-control" required>
                            <select name="actions[{{ $actionIndex }}][conditions][][operador]" class="form-select" style="width:100px">
                                @foreach(['=', '!=', '>', '<', '>=', '<='] as $op)
                                    <option value="{{ $op }}" {{ $cond->operador == $op ? 'selected' : '' }}>{{ $op }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="actions[{{ $actionIndex }}][conditions][][valor]"
                                value="{{ $cond->valor }}" placeholder="Valor" class="form-control" required>
                            <button type="button" class="btn btn-sm btn-danger remove-condicion">x</button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
