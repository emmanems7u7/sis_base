@extends('layouts.argon')

@section('content')
<style>
.seeder-item.seeder-checked {
    background-color: #d4edda; /* Verde suave */
    border-left: 4px solid #28a745;
    padding: 0.5rem 1rem; /* Más espacio arriba/abajo y a los lados */
    border-radius: 0.25rem; /* Esquinas redondeadas */
    margin-bottom: 0.25rem; /* Separación entre items */
}
</style>


<div class="row">
    <div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

            </div>
        </div>
    </div>
    </div>
    <div class="col-md-6">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">

            </div>
        </div>
    </div>
    </div>
</div>
   

    <div class="row mt-2">

        {{-- IZQUIERDA – EXPLORADOR --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">
                    <i class="fas fa-folder-tree me-2"></i> Seeders DEV
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @include('seeders.arbol', ['items' => $estructura])
                </div>
            </div>
        </div>

        {{-- CENTRO – SOLO DISEÑO --}}
        <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-bold text-center">
                Seeders Seleccionados
            </div>
            <div
                class="card-body"
                id="drop-zone"
                ondragover="allowDrop(event)"
                ondrop="dropSeeder(event)"
                style="min-height: 300px; max-height: 500px; overflow-y: auto; border: 2px dashed #ccc; padding: 1rem;"
            >
                <p class="text-muted text-center">Arrastra seeders aquí</p>
                <ul id="lista-seeders" class="list-group mb-0"></ul>
            </div>
        </div>
        </div>

        {{-- DERECHA – SOLO DISEÑO --}}
        <div class="col-md-4">
            <div class="row">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold text-center">
                    Destino
                </div>
                <div class="card-body text-muted text-center">
                    (QA / Producción)
                </div>
            </div>
            </div>

            <div class="row mt-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
            
            <button class="btn btn-sm btn-dark">Cancelar</button>
            <button class="btn btn-sm btn-success">Crear Solicitud</button>
            
            </div>
           
            </div>
        </div>

    </div>
    <script>
let seedersSeleccionados = [];

function dragSeeder(ev) {
    ev.dataTransfer.setData("ruta", ev.target.dataset.ruta);
    ev.dataTransfer.setData("nombre", ev.target.innerText.trim());
}

function allowDrop(ev) {
    ev.preventDefault();
}

function dropSeeder(ev) {
    ev.preventDefault();

    const ruta = ev.dataTransfer.getData("ruta");
    const nombre = ev.dataTransfer.getData("nombre");

    // Validar duplicado
    if (seedersSeleccionados.includes(ruta)) {
        alertify.warning('Este seeder ya fue seleccionado.');
        return;
    }

    seedersSeleccionados.push(ruta);

    // Crear li en la lista de seeders seleccionados
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `
        ${nombre}
        <button class="btn btn-sm btn-danger" onclick="eliminarSeeder('${ruta}', this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.getElementById('lista-seeders').appendChild(li);

    // Marcar el item original como seleccionado
    const seederOriginal = document.querySelector(`.seeder-item[data-ruta="${ruta}"]`);
    if (seederOriginal) {
        seederOriginal.classList.add('seeder-checked');
    }

    alertify.success('Seeder agregado.');
}

function eliminarSeeder(ruta, btn) {
    // Quitar de seleccionados
    seedersSeleccionados = seedersSeleccionados.filter(r => r !== ruta);
    btn.closest('li').remove();

    // Quitar marca visual del item original
    const seederOriginal = document.querySelector(`.seeder-item[data-ruta="${ruta}"]`);
    if (seederOriginal) {
        seederOriginal.classList.remove('seeder-checked');
    }

    alertify.success('Seeder eliminado.');
}
</script>

@endsection