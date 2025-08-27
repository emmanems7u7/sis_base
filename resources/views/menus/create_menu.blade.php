<div class="modal fade" id="crearMenuModal" tabindex="-1" aria-labelledby="crearMenuModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="crearMenuModalLabel">Crear Menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form action="{{ route('menus.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="seccion_id"
                            class="form-label @error('seccion_id') is-invalid @enderror">Sección</label>
                        <select name="seccion_id" id="seccion_id" class="form-select" required onchange="enviarId()">
                            <option value="" selected disabled>Selecciona una sección</option>
                            @foreach ($secciones as $seccion)
                                <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                    {{ $seccion->titulo }}
                                </option>
                            @endforeach
                        </select>
                        @error('seccion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label @error('nombre') is-invalid @enderror">Título del
                            Menú</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-control"
                            required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="orden" class="form-label @error('orden') is-invalid @enderror">Orden del
                            Menú</label>
                        <input type="text" name="orden" id="orden" value="{{ old('orden') }}" class="form-control"
                            required placeholder="1..">
                        @error('orden')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ruta" class="form-label @error('ruta') is-invalid @enderror">Ruta del Menú</label>
                        <select name="ruta" id="ruta" class="form-control" required>
                            <option value="">Seleccione una ruta</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->getName() }}" {{ old('ruta') == $route->getName() ? 'selected' : '' }}>
                                    {{ $route->getName() }}
                                </option>
                            @endforeach
                        </select>
                        @error('ruta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Menú</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    function enviarId() {
        var seccionId = document.getElementById('seccion_id').value;

        fetch("obtener/dato/menu", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                seccion_id: seccionId
            })
        })
            .then(response => response.json())
            .then(data => {

                if (data.status == 'success') {
                    document.getElementById('orden').setAttribute('placeholder', 'Sugerido: ' + data.sugerido);
                }

            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>