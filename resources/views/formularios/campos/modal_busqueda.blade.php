<!-- Modal de búsqueda -->
<div class="modal fade" id="modalBusqueda" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title">Buscar opción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="inputBusqueda" class="form-control" placeholder="Buscar...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnBuscar">Buscar</button>
            </div>
        </div>
    </div>
</div>