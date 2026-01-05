<div class="row">
    <div class="col-md-4">
        <h5 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Detalles Generales</h5>

        <p><strong>Nombre:</strong> {{ $modulo->nombre }}</p>
        <p><strong>Módulo Padre:</strong> {{ $modulo->modulo_padre_id }}</p>

        <p><strong>Creado el:</strong> {{ $modulo->created_at }}</p>
        <p><strong>Estado:</strong> {{ $modulo->activo ? 'Activo' : 'Inactivo' }}</p>
        <p>Formularios asociados:</p>
        @foreach ($modulo->formularios as $form)
            <span class="badge bg-secondary">{{ $form->nombre ?? 'ID ' . $form->id }}</span>
        @endforeach
    </div>
    <div class="col-md-7">

        {!! $modulo->descripcion !!}

        <div class="row">
            <div class="col-md-5">

                <h6>Formularios visibles en módulo</h6>
                @foreach ($modulo->formularios as $form)
                    <div class="form-check form-switch">
                        <input class="form-check-input toggle-formulario" type="checkbox" id="formulario_{{ $form->id }}"
                            data-modulo="{{ $modulo->id }}" data-formulario="{{ $form->id }}" {{ $form->pivot->activo ? 'checked' : '' }}>
                        <label class="form-check-label" for="formulario_{{ $form->id }}">
                            {{ $form->nombre }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="col-md-7 mb-3">
                <h6>Tipo de visualización de formularios</h6>
                @php
                    $config = $modulo->configuracion ?? [];
                @endphp



                <div class="form-check mb-2">
                    <input class="form-check-input config-radio" type="radio" name="config_modulo_{{ $modulo->id }}"
                        id="configMostrarTodos_{{ $modulo->id }}" data-modulo-id="{{ $modulo->id }}"
                        value='{"modo":"mostrar_todos"}' @if(isset($config['modo']) && $config['modo'] === 'mostrar_todos') checked @endif>
                    <label class="form-check-label" for="configMostrarTodos_{{ $modulo->id }}">
                        Mostrar todos los formularios
                    </label>
                </div>



                <div class="form-check mb-2">
                    <input class="form-check-input config-radio" type="radio" name="config_modulo_{{ $modulo->id }}"
                        id="configAcordeon_{{ $modulo->id }}" data-modulo-id="{{ $modulo->id }}"
                        value='{"modo":"acordeon"}' @if(isset($config['modo']) && $config['modo'] === 'acordeon') checked
                        @endif>
                    <label class="form-check-label" for="configAcordeon_{{ $modulo->id }}">
                        Modo acordeón
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input config-radio" type="radio" name="config_modulo_{{ $modulo->id }}"
                        id="configPestanas_{{ $modulo->id }}" data-modulo-id="{{ $modulo->id }}"
                        value='{"modo":"pestanas"}' @if(isset($config['modo']) && $config['modo'] === 'pestanas') checked
                        @endif>
                    <label class="form-check-label" for="configPestanas_{{ $modulo->id }}">
                        Modo pestañas
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input config-radio" type="radio" name="config_modulo_{{ $modulo->id }}"
                        id="configSelector_{{ $modulo->id }}" data-modulo-id="{{ $modulo->id }}"
                        value='{"modo":"selector"}' @if(isset($config['modo']) && $config['modo'] === 'selector') checked
                        @endif>
                    <label class="form-check-label" for="configSelector_{{ $modulo->id }}">
                        Seleccionar formularios
                    </label>
                </div>

            </div>

        </div>
    </div>
</div>


<script>
    document.querySelectorAll('.toggle-formulario').forEach(sw => {
        sw.addEventListener('change', function () {

            const payload = {
                modulo_id: this.dataset.modulo,
                formulario_id: this.dataset.formulario,
                activo: this.checked ? 1 : 0
            };

            fetch("{{ route('modulo.formulario.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(r => {
                    r.success
                        ? alertify.success(r.mensaje)
                        : alertify.error('No se pudo actualizar');
                })
                .catch(() => {
                    alertify.error('Error de conexión');
                    this.checked = !this.checked;
                });
        });
    });

    document.querySelectorAll('.config-radio').forEach(radio => {
        radio.addEventListener('change', async function () {
            const moduloId = this.dataset.moduloId;
            const config = JSON.parse(this.value);

            try {
                const res = await fetch(`/modulos/${moduloId}/configuracion`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ configuracion: config })
                });

                const data = await res.json();
                if (data.success) {
                    if (typeof alertify !== 'undefined') alertify.success('Configuración actualizada');
                } else {
                    if (typeof alertify !== 'undefined') alertify.error('Error al actualizar configuración');
                }
            } catch (err) {
                console.error(err);
                if (typeof alertify !== 'undefined') alertify.error('Error en la conexión');
            }
        });
    });
</script>