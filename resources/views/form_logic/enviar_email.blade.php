<div id="modal-email-block" class="d-none">

    <!-- FILA USUARIOS + ROLES -->
    <div class="row">

        <!-- USUARIOS -->
        <div class="col-md-6 mb-3">
            <label>Usuarios del sistema</label>

            <div class="d-flex gap-2">
                <select id="user-selector" class="form-select">
                    <option value="">-- Seleccionar usuario --</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-primary" id="add-user">
                    Agregar
                </button>
            </div>

            <ul class="list-group mt-2" id="user-list" style="max-height: 180px; overflow-y:auto;"></ul>

            <input type="hidden" name="usuarios[]" id="usuarios-hidden">
        </div>

        <!-- ROLES -->
        <div class="col-md-6 mb-3">
            <label>Roles del sistema</label> <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-original-title=" Se enviará el correo a todos los usuarios con los roles seleccionados, puede dejarlo vacio.">
            </i>

            <div class="border rounded p-2" style="max-height: 180px; overflow-y:auto;">
                @foreach($roles as $rol)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $rol->id }}"
                            id="rol_{{ $rol->id }}">
                        <label class="form-check-label" for="rol_{{ $rol->id }}">
                            {{ ucfirst($rol->name) }}
                        </label>
                    </div>
                @endforeach
            </div>

            <small class="text-muted">

            </small>
        </div>

    </div>

    <!-- ASUNTO -->
    <div class="mb-3">
        <label>Asunto</label>
        <input type="text" id="modal-email-subject" class="form-control" placeholder="Asunto del correo">
    </div>


    <div class="row">
        <div class="col-md-6">

            <!-- CAMPOS DEL FORMULARIO ORIGEN -->
            <div class="mb-2">
                <label class="small text-muted">
                    Campos del formulario de origen
                    <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Al seleccionar un campo, se agregará automáticamente al contenido del mensaje">
                    </i>
                </label>
                <div id="email-campos-origen" class="d-flex flex-wrap gap-1"></div>
            </div>

        </div>

        <div class="col-md-6">

            <!-- CAMPOS DE USUARIOS -->
            <div class="mb-2">
                <label class="small text-muted">
                    Campos de usuarios
                    <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
                        title="Al seleccionar un campo, se agregará automáticamente al contenido del mensaje">
                    </i>
                </label>
                <div id="email-campos-usuarios" class="d-flex flex-wrap gap-1"></div>
            </div>

        </div>
    </div>

    <!-- MENSAJE -->
    <div class="mb-3">
        <label>Mensaje</label>
        <textarea id="modal-email-body" class="form-control" rows="3" placeholder="Mensaje"></textarea>
    </div>
    <!-- PLANTILLA-->
    <div class="mb-2">
        <label>Plantilla</label>
        <select id="email-template" class="form-select form-select-sm">
            <option value="">-- Seleccionar plantilla --</option>


            @foreach($plantillas as $plantilla)
                <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
            @endforeach

        </select>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        let usuariosSeleccionados = [];

        document.getElementById('add-user').addEventListener('click', () => {
            const select = document.getElementById('user-selector');
            const userId = select.value;
            const userText = select.options[select.selectedIndex].text;

            if (!userId || usuariosSeleccionados.includes(userId)) {
                alertify.warning('Usuario ya agregado o no seleccionado.');
                return;

            }

            usuariosSeleccionados.push(userId);

            const li = document.createElement('li');
            li.dataset.id = userId;
            li.className = 'list-group-item d-flex justify-content-between align-items-center';

            const span = document.createElement('span');
            span.textContent = userText;

            const button = document.createElement('button');
            button.className = 'btn btn-xs btn-danger';
            button.textContent = 'X';

            li.appendChild(span);
            li.appendChild(button);

            li.querySelector('button').onclick = () => {
                alertify.confirm(
                    'Confirmación',
                    '¿Está seguro de quitar este usuario de la lista?',
                    function () {
                        // OK
                        usuariosSeleccionados = usuariosSeleccionados.filter(id => id !== userId);
                        document.getElementById('user-list').removeChild(li);
                        actualizarHidden();
                        alertify.success('Usuario eliminado');
                    },
                    function () {
                        // Cancel
                        alertify.message('Acción cancelada');
                    }
                );
            };
            document.getElementById('user-list').appendChild(li);
            actualizarHidden();
        });

        function actualizarHidden() {
            document.getElementById('usuarios-hidden').value = usuariosSeleccionados.join(',');
        }
    });
</script>