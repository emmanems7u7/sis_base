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
            <h5>Formularios visibles en módulo</h5>
            @foreach ($modulo->formularios as $form)
            <div class="form-check form-switch">
                <input
                    class="form-check-input toggle-formulario"
                    type="checkbox"
                    id="formulario_{{ $form->id }}"
                    data-modulo="{{ $modulo->id }}"
                    data-formulario="{{ $form->id }}"
                    {{ $form->pivot->activo ? 'checked' : '' }}
                >
                <label class="form-check-label" for="formulario_{{ $form->id }}">
                    {{ $form->nombre }}
                </label>
            </div>
        @endforeach
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
</script>