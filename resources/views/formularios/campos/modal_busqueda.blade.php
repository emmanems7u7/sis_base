<div class="modal fade" id="modalBusqueda" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div
            class="modal-content border-0 rounded-4 shadow {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">

            <div class="modal-body p-3 position-relative">

                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

                <div class="d-flex justify-content-center mb-3">
                    <div style="width:36px;height:4px;border-radius:999px;background:#adb5bd;opacity:.45;"></div>
                </div>

                <h6 class="text-center mb-3 fw-semibold">
                    Buscar opción
                </h6>

                <input type="text" id="inputBusqueda" class="form-control" placeholder="Escriba para buscar...">

                <div class="d-grid mt-3">
                    <button type="button" class="btn btn-primary" id="btnBuscar">
                        Buscar
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>
