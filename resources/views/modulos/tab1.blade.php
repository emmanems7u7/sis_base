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

</div>