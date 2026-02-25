@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Constructor de Formulario: {{ $formulario->nombre }}</h5>

                    </div>
                    <a href="{{ route('formularios.index') }}" class="btn btn-secondary btn-sm"><i
                            class="fas fa-arrow-left me-1"></i>Volver a Formularios</a>


                </div>
            </div>
        </div>
        <div class="col-md-6 mt-3">

            <div class="card shadow-lg">
                <div class="card-body">
                    <h5><i class="fas fa-edit me-2"></i>Construcción de Formularios</h5>

                    <small><i class="fas fa-info-circle me-1"></i>En este módulo puedes diseñar y gestionar formularios
                        personalizados para cualquier propósito dentro del sistema.</small><br>



                    <small><i class="fas fa-arrows-alt me-1"></i>Los campos se pueden <strong>arrastrar</strong> para
                        cambiar su
                        orden de aparición dentro del formulario. El cambio se guarda automáticamente al soltar el
                        campo.</small><br>

                    <small><i class="fas fa-copy me-1"></i>Los formularios pueden ser reutilizados y exportados para
                        distintos
                        módulos según la necesidad.</small><br>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-lg mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-1 mb-3">
                        <h5 class="mb-0">Crear/Editar Campos</h5>
                        <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Algunos campos, como Checkbox, Radio y Selector, requieren seleccionar una categoría para definir las opciones disponibles."></i>
                    </div>
                    <!-- Formulario de creación/edición de campo -->
                    <form id="formCampo" method="POST" action="{{ route('campos.store', $formulario) }}">
                        @csrf

                        <div class="row g-3 align-items-end">
                            <!-- Tipo de campo -->
                            <div class="">
                                <label for="tipoCampo" class="form-label">Tipo</label>
                                <select id="tipoCampo" name="tipo" class="form-select shadow-sm">
                                    <option value="" selected>Seleccione un tipo</option>
                                    @foreach ($campos_formulario as $campoForm)
                                        <option value="{{ $campoForm->catalogo_codigo }}">{{ $campoForm->catalogo_descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Nombre interno -->
                            <div class="position-relative">
                                <label for="nombreCampo" class="form-label d-flex align-items-center gap-1">
                                    Nombre interno
                                    <i class="fas fa-exclamation-triangle text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Recuerda siempre revisar que los nombres internos sean únicos dentro del formulario para evitar conflictos en la captura de datos."></i>
                                </label>
                                <input id="nombreCampo" name="nombre" type="text" class="form-control shadow-sm"
                                    placeholder="Nombre interno">
                            </div>

                            <!-- Etiqueta visible -->
                            <div class="">
                                <label for="etiquetaCampo" class="form-label">Etiqueta visible</label>
                                <input id="etiquetaCampo" name="etiqueta" type="text" class="form-control shadow-sm"
                                    placeholder="Etiqueta visible">
                            </div>

                            <!-- Requerido -->
                            <div class=" d-flex align-items-center">
                                <div class="form-check d-flex align-items-center gap-1">
                                    <input type="checkbox" id="requeridoCampo" name="requerido" class="form-check-input">
                                    <label class="form-check-label mb-0" for="requeridoCampo">Requerido</label>
                                    <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Marca la casilla Requerido si deseas que el campo sea obligatorio al completar el formulario."></i>
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div id="ContenedorCategoria" style="display:none;">
                                <div class="row">

                                    <div class="" id="categorias_cont">
                                        <label for="categoriaCampo" class="form-label">Categoría</label> <i
                                            class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Seleccione una categoría que se utilizará para completar el tipo seleccionado."></i>
                                        <select id="categoriaCampo" name="categoria_id" class="form-select">
                                            <option value="">-- Seleccionar categoría --</option>
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="" id="formularios_cont">
                                        <label for="formularioCampo" class="form-label">Formulario</label> <i
                                            class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            title="Seleccione una categoría que se utilizará para completar el tipo seleccionado."></i>
                                        <select id="formularioCampo" name="formulario_id" class="form-select">
                                            <option value="">-- Seleccionar formulario --</option>
                                            @foreach($formularios as $form)
                                                <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <!-- Formulario -->
                                    <div class="  d-flex align-items-center ">
                                        <div class="form-check d-flex align-items-center gap-1">
                                            <input type="checkbox" id="formulario_campo" name="formulario"
                                                class="form-check-input">
                                            <label class="form-check-label mb-0" for="formulario_campo">Seleccionar un
                                                formulario</label>
                                            <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Marca la casilla si deseas que el campo sean registros de un formulario"></i>
                                        </div>
                                    </div>
                                </div>


                            </div>


                            <!-- Botones -->
                            <div class="col-md-12 d-flex gap-2 mt-2">
                                <button id="btnAgregarCampo" class="btn btn-sm btn-primary">Agregar campo</button>
                                <button type="button" id="btnCancelarEdicion" class="btn btn-secondary"
                                    style="display:none;">Cancelar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>

        <div class="col-md-8">
            <div class="card  shadow-lg mt-3">
                <div class="card-body">
                    
                <h5>Configuracion de los Campos</h5>

                </div>
            </div>

            

            <div class="card mt-2 shadow-lg">
    <div class="card-body">

        <!-- Tabs de configuración -->
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-concatenado" data-bs-toggle="tab" data-bs-target="#content-concatenado" type="button" role="tab" aria-controls="content-concatenado" aria-selected="true">
                    <i class="fas fa-link me-1"></i> Concatenado
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-validaciones" data-bs-toggle="tab" data-bs-target="#content-validaciones" type="button" role="tab" aria-controls="content-validaciones" aria-selected="false">
                    <i class="fas fa-check-circle me-1"></i> Validaciones
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-avanzadas" data-bs-toggle="tab" data-bs-target="#content-avanzadas" type="button" role="tab" aria-controls="content-avanzadas" aria-selected="false">
                    <i class="fas fa-cogs me-1"></i> Opciones Avanzadas
                </button>
            </li>
        </ul>

        <div class="tab-content p-4 border border-top-0 rounded-bottom" id="configTabsContent">
            
            <!-- Pestaña Concatenado -->
          @include('formularios.campos.configuracion_concatenado')
            <!-- Pestaña Validaciones -->
            <div class="tab-pane fade" id="content-validaciones" role="tabpanel" aria-labelledby="tab-validaciones">
                <p class="text-muted small mb-0">Agrega reglas de validación o campos obligatorios aquí.</p>
            </div>

            @include('formularios.campos.opciones_avanzadas')


        </div>
    </div>
</div>

        </div>
    </div>



    <!-- Lista de campos -->
    <div id="listaCampos" class="row g-2 mb-2 mt-2">
        @foreach($campos as $campo)
            <div class="col-lg-4 col-md-6 col-sm-12" data-id="{{ $campo->id }}">
                <div class="card p-1 shadow-sm position-relative">
                    <!-- Ícono de arrastre en la esquina superior derecha -->
                    <span class="drag-handle position-absolute top-0 end-0 p-1" style="cursor: grab;">
                        <i class="fas fa-arrows-alt"></i>
                    </span>

                    <div class="card-body p-2">
                        @include('formularios.campos.lista_campos', ['campo' => $campo])
                    </div>

                    <div class="card-footer p-2">
                        <div class="d-flex justify-content-between align-items-center">

                            <!-- Botones -->
                            <div class="d-flex gap-1">
                                <button class="btn btn-xs btn-warning btnEditarCampo" data-id="{{ $campo->id }}">
                                    Editar
                                </button>

                                <a class="btn btn-xs btn-danger"
                                    onclick="eliminarCampo('eliminarCampo_{{ $campo->id }}',{{ $campo->id }})">
                                    Eliminar
                                </a>

                                <form id="eliminarCampo_{{ $campo->id }}" action="{{ route('campos.destroy', $campo) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>

                            <!-- Check visible en listado -->
                            <div class="form-check m-0">
                                <input class="form-check-input toggle-visible-listado" type="checkbox"
                                    data-id="{{ $campo->id }}" {{ $campo->config['visible_listado'] ?? false ? 'checked' : '' }}>
                                <label class="form-check-label small" for="visible_listado_{{ $campo->id }}">
                                    Visible
                                </label>
                                <i class="fas fa-question-circle text-primary d-none d-md-inline" data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Activa esta opción para que el campo aparezca en el listado principal. Si está desactivada, el campo se mostrará únicamente al presionar el botón 'Ver'. Esto es útil si tienes muchos campos">
                                </i>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @include('formularios.campos.modal_busqueda')
    <script>
    window.routes = {
        reordenarCampos: @json(route('formularios.campos.reordenar', $formulario))
    };
</script>
    <script>
        let editId = null;

        // Mostrar selector de categoría según tipo
        const tipoCampo = document.getElementById('tipoCampo');
        const categoriaCampo = document.getElementById('ContenedorCategoria');

        tipoCampo.addEventListener('change', function () {

            const tipoTexto = this.options[this.selectedIndex].textContent.trim();
            if (['Radio', 'Checkbox', 'Selector', 'Imagen', 'Video', 'Archivo'].includes(tipoTexto)) {

                categoriaCampo.style.display = '';
                document.getElementById('categoriaCampo').style.display = 'inline-block';
            } else {


                categoriaCampo.style.display = 'none';
                categoriaCampo.value = '';
            }
        });

    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.toggle-visible-listado').forEach(function (checkbox) {

                checkbox.addEventListener('change', function () {

                    let campoId = this.dataset.id;
                    let visible = this.checked ? 1 : 0;

                    fetch("{{ route('campos.toggleVisible') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            campo_id: campoId,
                            visible_listado: visible
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            alertify.success(data.message);
                        })
                        .catch(error => {
                            console.error("Error:", error);
                        });

                });

            });

        });

  



    </script>
<style>
/* Clase para el ghost mientras arrastras */
.drag-ghost {
    background-color: rgba(108, 117, 125, 0.25); /* equivalente a bg-secondary + bg-opacity-25 */
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

const constructor = document.getElementById('constructorConcatenado');
const textoAyuda = document.getElementById('textoAyuda');
const guardarBtn = document.getElementById('guardarConcatenado');

if(!constructor || !textoAyuda){
    console.error('Constructor o textoAyuda no encontrado');
    return;
}

function actualizarTextoAyuda(){
    const tieneElementos = [...constructor.children].some(e => e.tagName === 'SPAN');
    textoAyuda.style.display = tieneElementos ? 'none' : 'inline';
}
function agregarElementoConstructor(data){
    if(!data) return;

    const span = document.createElement('span');
    span.classList.add(
        'badge', 
        data.type === 'campo' ? 'bg-info' : 'bg-warning', 
        'me-1', 
        'd-inline-flex', 
        'align-items-center'
    );

    // Ajuste de estilo compacto
    span.style.padding = '0.25rem 0.5rem'; // padding más pequeño
    span.style.fontSize = '0.75rem';       // fuente más pequeña
    span.style.cursor = 'grab';
    span.draggable = true;

    // Si es separador y es un espacio, usamos el token [ESPACIO]
    if(data.type === 'separador' && data.sep === ' '){
        span.innerText = '[ESPACIO]';
        span.dataset.sep = ' ';
    } else {
        span.innerText = data.label;
        if(data.type === 'separador') span.dataset.sep = data.sep;
    }

    span.dataset.type = data.type;
    if(data.type === 'campo') span.dataset.id = data.id;

    // Botón de eliminar
    const btnEliminar = document.createElement('button');
    btnEliminar.type = 'button';
    btnEliminar.innerHTML = '&times;';
    btnEliminar.classList.add('btn-close', 'btn-close-white', 'ms-1', 'p-0');
    btnEliminar.style.fontSize = '0.6rem';
    btnEliminar.addEventListener('click', () => {
        span.remove();
        actualizarTextoAyuda();
    });

    span.appendChild(btnEliminar);
    constructor.appendChild(span);
    actualizarTextoAyuda();
}
// -------------------
// Reconstruir desde JSON existente
// -------------------
const configConcatenado = {!! json_encode($formulario->config['configuracion_concatenado'] ?? null) !!};
const camposDisponibles = {!! json_encode($campos->map(function($c){ return ['id'=>$c->id, 'label'=>$c->etiqueta]; })) !!};
if(configConcatenado){
    const estructura = configConcatenado.estructura || '';
    // recorrer cada "elemento" según separadores o IDs
    // Nota: asumimos que separadores son todos los caracteres que no sean IDs
    let buffer = '';
    for(let i=0; i<estructura.length; i++){
        const char = estructura[i];
        // si es número (ID) asumimos que es un campo
        if(/[0-9]/.test(char)){
            buffer += char;
            // chequeo si el siguiente no es número o es fin de string => guardar campo
            if(i+1 >= estructura.length || !/[0-9]/.test(estructura[i+1])){
                const campo = camposDisponibles.find(c => c.id == buffer);
                if(campo){
                    agregarElementoConstructor({type:'campo', id: campo.id, label: campo.label});
                }
                buffer = '';
            }
        } else {
           
            agregarElementoConstructor({type:'separador', sep: char, label: char});
        }
    }
}

// -------------------
// Drag start lista de campos
document.querySelectorAll('.campo-item').forEach(c => {
    c.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: 'campo',
            id: c.dataset.id,
            label: c.innerText
        }));
    });
});

// Drag start separadores
document.querySelectorAll('.separador-item').forEach(s => {
    s.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            type: 'separador',
            sep: s.dataset.sep,
            label: s.innerText
        }));
    });
});

// Drop externo seguro
constructor.addEventListener('dragover', e => e.preventDefault());
constructor.addEventListener('drop', e => {
    e.preventDefault();
    try {
        const dataText = e.dataTransfer.getData('text/plain');
        if(!dataText) return;
        const data = JSON.parse(dataText);
        agregarElementoConstructor(data);
    } catch(err){
        // ignorar drops internos (Sortable)
    }
});

// Sortable
Sortable.create(constructor, {
    animation: 150,
    handle: 'span',
    ghostClass: 'drag-ghost'
});

// Guardar concatenado
guardarBtn.addEventListener('click', async () => {
    const elementos = [...constructor.children].map(e => {
        if(e.dataset.type === 'campo') return e.dataset.id;
        if(e.dataset.type === 'separador') return e.dataset.sep;
        return '';
    });

    const idsCampos = [...constructor.children]
        .filter(e => e.dataset.type === 'campo')
        .map(e => e.dataset.id);

    const visual = [...constructor.children].map(e => e.innerText.replace('×','')).join('');

    const formId = {{ $formulario->id }};
    try{
        const res = await fetch(`/formularios/${formId}/guardar-concatenado`, {
            method: 'POST',
            headers: {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ids: idsCampos,
                estructura: elementos.join('')
            })
        });
        const data = await res.json();
        if(data.success){
            alertify.success('Configuración guardada correctamente');
        }
    }catch(err){
        console.error(err);
    }
});

actualizarTextoAyuda();

});
</script>
@endsection