<div class="modal fade" id="crearSeccionModal" tabindex="-1" aria-labelledby="crearSeccionModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="crearSeccionModalLabel">Crear Sección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('secciones.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="titulo" class="form-label @error('titulo') is-invalid @enderror">Título</label>
                        <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" class="form-control"
                            required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="icono" class="form-label @error('icono') is-invalid @enderror">Icono</label>
                        <div class="input-group">
                            <input type="text" name="icono" id="icono" value="{{ old('icono') }}" class="form-control"
                                required placeholder="fas fa-user">
                            <span class="input-group-text">
                                <i id="preview-icono" class="{{ old('icono') }}"></i>
                            </span>
                        </div>
                        @error('icono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar sección</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputIcono = document.getElementById('icono');
        const previewIcono = document.getElementById('preview-icono');

        inputIcono.addEventListener('input', function () {
            const valor = inputIcono.value.trim();
            previewIcono.className = valor;
        });
    });


</script>

<script>
    let debounceTimer;

    document.getElementById('titulo').addEventListener('input', function () {
        clearTimeout(debounceTimer);

        const input = this;

        debounceTimer = setTimeout(() => {
            const titulo = input.value.trim();

            if (titulo.length < 3) return;

            fetch("/api/sugerir-icono", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ titulo })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.icono) {
                        document.getElementById('icono').value = data.icono;
                        document.getElementById('preview-icono').className = data.icono;
                    }
                })
                .catch(err => console.error(err));
        }, 500); // espera 500ms después de la última tecla
    });
</script>