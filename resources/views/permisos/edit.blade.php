<div class="modal fade" id="modalEditarPermiso" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditarPermiso" method="POST">
            @csrf
            @method('PUT')
            <div
                class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Permiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editarPermisoId">
                    <label>Nombre</label>
                    <input type="text" name="name" id="editarPermisoNombre" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function editarPermiso(id) {
        fetch(`/permissions/edit/${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const permiso = data.permission;
                    document.getElementById('editarPermisoId').value = permiso.id;
                    document.getElementById('editarPermisoNombre').value = permiso.name;

                    const form = document.getElementById('formEditarPermiso');
                    form.action = `/permissions/${permiso.id}`;

                    const modal = new bootstrap.Modal(document.getElementById('modalEditarPermiso'));
                    modal.show();
                }
            });
    }
</script>