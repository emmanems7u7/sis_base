@php
    $nombreVal = old('nombre', $modulo->nombre ?? '');
    $descripcionVal = old('descripcion', $modulo->descripcion ?? '');
    $padreVal = old('modulo_padre_id', $modulo->modulo_padre_id ?? '');
    $activoVal = old('activo', isset($modulo) ? $modulo->activo : true);

    // Obtener los formularios seleccionados
    $formulariosSeleccionados = [];

    // Si hay old data de formularios (tras error de validación)
    if ($oldFormularios = old('formularios')) {
        foreach ($oldFormularios as $f) {
            // Si solo es un ID, dejamos nombre y descripcion vacíos
            $formulariosSeleccionados[] = [
                'id' => $f,
                'nombre' => '',
                'descripcion' => '',
            ];
        }
    }
    // Si no hay old data, cargamos desde la base de datos
    elseif (isset($modulo)) {
        $formulariosSeleccionados = $modulo->formularios->map(fn($f) => [
            'id' => $f->id,
            'nombre' => $f->nombre,
            'descripcion' => (string) $f->descripcion,
        ])->toArray();
    }



@endphp
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Nombre del Módulo</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                value="{{ $nombreVal }}">
            @error('nombre')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">

        <div class="mb-3">
            <label class="form-label">Módulo Padre</label>
            <i class="fas fa-question-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                title="Si este módulo depende de otro, selecciónalo; si no, deja vacío"></i>
            <select name="modulo_padre_id" class="form-select @error('modulo_padre_id') is-invalid @enderror">
                <option value="">— Ninguno —</option>
                @foreach($modulosPadre as $padre)
                    <option value="{{ $padre->id }}" @if((string) $padreVal === (string) $padre->id) selected @endif>
                        {{ $padre->nombre }}
                    </option>
                @endforeach
            </select>
            @error('modulo_padre_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

    </div>

</div>


<div class="mb-3">
    <label class="form-label">Descripción</label>
    <i class="fas fa-exclamation-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
        title="Aquí puedes agregar contenido HTML, clases de Bootstrap, iconos FontAwesome y estilos, pero NO se permite contenido JavaScript ni eventos inline como onclick."></i>
    <!-- Barra de herramientas estilo CKEditor -->
    <div class="editor-toolbar d-flex align-items-center border bg-dark_code px-2 py-1">
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('div')">Div</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('h4')">H4</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('h5')">H5</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('small')">Small</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('icon')">Icono</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1"
            onclick="insertHTML('grid')">Grid</button>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto py-0 px-1" onclick="vistaPrevia()">
            <i class="fas fa-play me-1"></i>Vista Previa
        </button>
    </div>


    <!-- Editor de código -->
    <textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
        rows="10" style="border-top-left-radius:0; border-top-right-radius:0;">{{ $descripcionVal }}</textarea>

    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<!-- Modal de Vista Previa -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }} ">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Vista Previa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



{{-- === Asociar formularios dinámicamente === --}}
<div class="mb-3">
    <label class="form-label">Asociar Formularios</label>
    <div class="input-group">
        <select id="selectFormulario" class="form-select">
            <option value="">Seleccione un formulario...</option>
            @foreach($formularios as $form)
                <option value="{{ $form->id }}" data-nombre="{{ $form->nombre }}"
                    data-descripcion="{{ $form->descripcion }}">
                    {{ $form->nombre }}
                </option>
            @endforeach
        </select>
        <button type="button" id="btnAgregarFormulario" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar
        </button>
    </div>
    @error('formularios')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
<div class="table-responsive">
    <table class="table table-bordered mt-3" id="tablaFormularios">
        <thead class="table table-striped align-middle">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="text-center" style="width: 100px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            {{-- Formularios seleccionados --}}
            @if(!empty($formulariosSeleccionados))
                @foreach($formulariosSeleccionados as $f)
                    <tr data-id="{{ $f['id'] }}">
                        <td>{{ $f['nombre'] }}</td>
                        <td>{{ $f['descripcion'] }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger btnEliminarFormulario">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                        <input type="hidden" name="formularios[]" value="{{ $f['id'] }}">
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
<div class="form-check mb-3">
    <input class="form-check-input @error('activo') is-invalid @enderror" type="checkbox" name="activo" id="activo"
        value="1" @if((bool) $activoVal) checked @endif>
    <label class="form-check-label" for="activo">Activo</label>
    @error('activo')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<script>
    let editor;

    document.addEventListener('DOMContentLoaded', function () {
        editor = CodeMirror.fromTextArea(document.getElementById('descripcion'), {
            mode: 'htmlmixed',
            theme: 'dracula',
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            autoCloseTags: true,
        });

        editor.getWrapperElement().style.fontSize = '13px';
    });

    function insertHTML(campo) {
        if (!editor) return;
        let html = '';
        switch (campo) {
            case 'div':
                html = '<div></div>\n';
                break;
            case 'h4':
                html = '<h4>Título H4</h4>\n';
                break;
            case 'h5':
                html = '<h5>Título H5</h5>\n';
                break;
            case 'small':
                html = '<small>Texto pequeño</small>\n';
                break;
            case 'icon':
                html = '<i class="fas fa-info-circle"></i>\n';
                break;
            case 'grid':
                html = `<div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6"></div>
                        </div>\n`;
                break;
        }

        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(html, cursor);
        editor.focus();
    }

    function vistaPrevia() {
        if (!editor) return;

        let html = editor.getValue();
        let contieneScripts = false;

        if (/<script[\s\S]*?>[\s\S]*?<\/script>/gi.test(html)) {
            contieneScripts = true;
        }

        if (/\s*on\w+="[^"]*"/gi.test(html)) {
            contieneScripts = true;
        }

        html = html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
        html = html.replace(/\s*on\w+="[^"]*"/gi, '');

        if (contieneScripts) {
            alertify.warning('Los scripts y eventos inline han sido eliminados para la vista previa por seguridad. Recuerda que el sistema no dejará que guardes la Descripción si tiene contenido Javascript');
        }

        const previewDiv = document.getElementById('previewContent');
        previewDiv.innerHTML = html;

        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    }


</script>


<script>



    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('selectFormulario');
        const btnAgregar = document.getElementById('btnAgregarFormulario');
        const tabla = document.getElementById('tablaFormularios').querySelector('tbody');

        btnAgregar.addEventListener('click', () => {
            const selectedOption = select.options[select.selectedIndex];
            const id = selectedOption.value;
            const nombre = selectedOption.dataset.nombre;
            const descripcion = selectedOption.dataset.descripcion;

            if (!id) return alertify.error('Seleccione un formulario primero.');

            // Verificar si ya está en la tabla actual
            if (tabla.querySelector(`tr[data-id="${id}"]`)) {
                return alertify.warning('Este formulario ya está agregado en el módulo.');
            }

            // AJAX para verificar si ya está asociado a otro módulo
            fetch(`/modulos/formulario/check/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        alertify.confirm(
                            'Formulario ya asignado',
                            `Este formulario ya está asociado al módulo "${data.modulo}". ¿Desea continuar y asignarlo también?`,
                            function () {
                                agregarFila(id, nombre, descripcion);
                            },
                            function () {
                                alertify.message('Operación cancelada.');
                            }
                        );
                    } else {
                        agregarFila(id, nombre, descripcion);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alertify.error('Error al verificar el formulario.');
                });
        });

        function agregarFila(id, nombre, descripcion) {
            const tr = document.createElement('tr');
            tr.dataset.id = id;
            tr.innerHTML = `
            <td>${nombre}</td>
            <td>${descripcion || ''}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger btnEliminarFormulario">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
            <input type="hidden" name="formularios[]" value="${id}">
        `;
            tabla.appendChild(tr);
            alertify.success(`Formulario "${nombre}" agregado.`);
        }

        // Eliminar fila
        tabla.addEventListener('click', (e) => {
            if (e.target.closest('.btnEliminarFormulario')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>