{{-- ===============================
NOMBRE / FORMULARIO
=============================== --}}
<label class="form-label">Nombre</label>

<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" id="chkFormulario">
    <label class="form-check-label" for="chkFormulario">
        Generar permiso desde formulario
    </label>
</div>

<input type="text" name="name" id="inputNombre" class="form-control mb-3" required>

<select id="selectFormulario" class="form-select mb-3 d-none">
    <option value="">-- Seleccione un formulario --</option>
    @foreach ($formularios as $formulario)
        <option value="{{ $formulario->id }}">
            {{ $formulario->nombre }}
        </option>
    @endforeach
</select>
{{-- ===============================
TIPOS DE PERMISO
=============================== --}}
<label class="form-label">Tipos de permiso</label>

<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" id="chkSelectAll">
    <label class="form-check-label" for="chkSelectAll">
        Seleccionar todos
    </label>
</div>

@foreach ($catalogo_permisos as $permisoC)
    <div class="d-flex align-items-center gap-3 mb-2">
        <div class="form-check mb-0">
            <input class="form-check-input chk-permiso" type="checkbox" value="{{ $permisoC->catalogo_codigo }}"
                data-nombre="{{ $permisoC->catalogo_descripcion }}">
            <label class="form-check-label">
                {{ $permisoC->catalogo_descripcion }}
            </label>
        </div>

        {{-- Preview al lado del checkbox --}}
        <span class="text-muted small preview-permiso"></span>
    </div>
@endforeach


{{-- ===============================
INPUTS OCULTOS (BACKEND)
=============================== --}}
<div id="permisosHidden"></div>



{{-- ===============================
SCRIPT
=============================== --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chkFormulario = document.getElementById('chkFormulario');
        const inputNombre = document.getElementById('inputNombre');
        const selectFormulario = document.getElementById('selectFormulario');
        const permisosHidden = document.getElementById('permisosHidden');
        const chkSelectAll = document.getElementById('chkSelectAll');

        // Toggle entre input y select
        chkFormulario.addEventListener('change', () => {
            toggleBase();
            actualizarTodos();
            construirPermisos();
        });

        inputNombre.addEventListener('input', () => {
            actualizarTodos();
            construirPermisos();
        });

        selectFormulario.addEventListener('change', () => {
            actualizarTodos();
            construirPermisos();
        });

        // Checkboxes de permisos del catálogo
        document.querySelectorAll('.chk-permiso').forEach(chk => {
            chk.addEventListener('change', () => {
                actualizarFila(chk);
                construirPermisos();
                sincronizarSelectAll();
            });
        });

        // ===============================
        // SELECT ALL
        // ===============================
        chkSelectAll.addEventListener('change', () => {
            const todos = document.querySelectorAll('.chk-permiso');
            todos.forEach(chk => {
                chk.checked = chkSelectAll.checked;
                actualizarFila(chk);
            });
            construirPermisos();
        });

        function sincronizarSelectAll() {
            const todos = document.querySelectorAll('.chk-permiso');
            const todosMarcados = Array.from(todos).every(chk => chk.checked);
            chkSelectAll.checked = todosMarcados;
        }

        /* ===============================
           Toggle input / select
        =============================== */
        function toggleBase() {
            if (chkFormulario.checked) {
                inputNombre.classList.add('d-none');
                inputNombre.removeAttribute('required');
                selectFormulario.classList.remove('d-none');
            } else {
                inputNombre.classList.remove('d-none');
                inputNombre.setAttribute('required', true);
                selectFormulario.classList.add('d-none');
                selectFormulario.value = '';
            }
        }

        /* ===============================
           BASE PARA BACKEND (ID o TEXTO)
        =============================== */
        function obtenerBaseBackend() {
            return chkFormulario.checked
                ? selectFormulario.value   // catalogo_codigo
                : inputNombre.value.trim(); // texto directo
        }

        /* ===============================
           BASE PARA PREVIEW (NOMBRE)
        =============================== */
        function obtenerBasePreview() {
            if (chkFormulario.checked) {
                const option = selectFormulario.options[selectFormulario.selectedIndex];
                return option ? option.text.trim() : '';
            }
            return inputNombre.value.trim();
        }

        /* ===============================
           PREVIEW POR FILA
        =============================== */
        function actualizarFila(chk) {
            const preview = chk.closest('.d-flex').querySelector('.preview-permiso');
            const base = obtenerBasePreview();

            if (chk.checked && base) {
                preview.textContent = `${base}.${chk.dataset.nombre}`;
            } else {
                preview.textContent = '';
            }
        }

        function actualizarTodos() {
            document.querySelectorAll('.chk-permiso').forEach(chk => actualizarFila(chk));
            sincronizarSelectAll();
        }

        /* ===============================
           INPUTS OCULTOS PARA BACKEND
        =============================== */
        function construirPermisos() {
            permisosHidden.innerHTML = '';
            const base = obtenerBaseBackend();

            if (!base) return;

            document.querySelectorAll('.chk-permiso:checked').forEach(chk => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'permisos[]';
                input.value = `${base}.${chk.dataset.nombre}`;
                permisosHidden.appendChild(input);
            });
        }

        // Inicializar previsualización
        actualizarTodos();
        construirPermisos();
    });
</script>