@php
    $isEdit = isset($rule);
@endphp



<div class="modal fade" id="modalVerAccion" tabindex="-1"data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- Sección principal --}}

<div class="row mt-2">

<div class="col-md-3">
<div class="card shadow-sm border-0">
        
       
        <div class="card-body">

            <div class="row g-3">

                <!-- Nombre -->
                <div class="col-12">
                    <label class="form-label small text-muted">Nombre de la Regla</label>
                    <input type="text" 
                           name="nombre" 
                           class="form-control shadow-sm"
                           placeholder="Ej: Enviar correo al crear registro"
                           value="{{ old('nombre', $rule->nombre ?? '') }}" 
                           required>
                </div>

                <!-- Formulario -->
                <div class="col-12">
                    <label class="form-label small text-muted">Formulario de origen</label>
                    <select class="form-select shadow-sm select-formulario" 
                            id="formulario_id" 
                            name="formulario_id" 
                            required>
                        <option value="">Seleccione un formulario...</option>

                        @foreach ($formularios as $form)
                            <option value="{{ $form->id }}" 
                                {{ (old('formulario_id', $rule->formulario_id ?? '') == $form->id) ? 'selected' : '' }}>
                                {{ $form->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Evento -->
                <div class="col-12">
                    <label class="form-label small text-muted">Evento</label>
                    <select name="evento" 
                            class="form-select shadow-sm tipo_valor_principal" 
                            required>
                        @php
                            $eventos = [
                                'on_create' => 'Al Crear',
                                'on_update' => 'Al Actualizar',
                                'on_delete' => 'Al Eliminar'
                            ];
                        @endphp

                        @foreach($eventos as $key => $label)
                            <option value="{{ $key }}" 
                                {{ (old('evento', $rule->evento ?? '') == $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Tipo de acción --}}
                <div class="col-12">
                    <label class="form-label small text-muted">Tipo de Acción</label>
                    <select id="modal-tipo-accion" class="form-select shadow-sm" required>
                        <option value="" disabled selected>Seleccionar tipo de Acción</option>
                        @foreach ($tipo_acciones as $tipo_accion)
                            <option value="{{ $tipo_accion->catalogo_codigo }}">{{ $tipo_accion->catalogo_descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Activo -->
                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="activo" 
                               id="activo"
                               {{ old('activo', $rule->activo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="activo">
                            Regla Activa
                        </label>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <h5>Acciones</h5>
<div id="acciones-list" class=" row mb-3"></div>


</div>
    <div class="col-md-9">

    <div class="card shadow-sm border-0 h-100">
        <div class="card-body d-flex flex-column">
              
    
                {{-- Bloque modificar campo --}}
                @include('form_logic.modificar_campo')

                {{-- Bloque crear registros --}}


                @include('form_logic.crear_registros')



                {{-- Bloque enviar email --}}
                @include('form_logic.enviar_email')
                
                <div id="contenedor_mensaje"
                    class="flex-fill d-flex flex-column justify-content-center align-items-center text-center text-muted">

                    <i class="fas fa-filter fa-4x mb-3 opacity-50"></i>

                    <h5 class="fw-semibold">Agregue sus Acciones</h5>

                    <small>
                        Presione el botón para crear una nueva acción
                    </small>
                    <button type="button" class="btn btn-primary mb-3" id="open-modal-accion">+ Agregar Acción</button>

                </div>


                <div id="contenedor_condiciones" class="d-none">
                <div class="mt-auto d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-primary" id="add-condicion-modal">
                        + Agregar Condición
                    </button>
                </div>
              
              
                <div id="condiciones-modal-container"></div>

                <template id="condicion-modal-template">
                    <div class="condicion-block mb-2 p-2 border rounded">Condición <strong id="num_condicion"></strong>
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <select class="form-select cond-form-origen">
                                    <option value="">-- Seleccione campo origen --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select cond-operador">
                                    <option value="=">=</option>
                                    <option value="!=">!=</option>
                                    <option value=">">></option>
                                    <option value="<">
                                        < </option>
                                    <option value=">=">>=</option>
                                    <option value="<=">
                                        <= </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select cond-form-destino">
                                    <option value="">-- Seleccione campo destino --</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex justify-content-center">
                                <button type="button" class="btn btn-sm btn-danger remove-condicion-modal">x</button>
                            </div>
                        </div>
                    </div>
                </template>

                </div>

                <div class="d-flex gap-2 justify-content-center d-none" id="contenedor_botones" >
                    <button 
                        type="button" 
                        class="btn btn-secondary" 
                        id="cancelar-edicion-accion">
                        Cancelar
                    </button>

                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        id="guardar-accion-modal">
                        Agregar Acción
                    </button>
                </div>

            </div>
    </div>
    </div>

</div>