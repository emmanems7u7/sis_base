<div class="modal fade" id="crearMenuModal" tabindex="-1" aria-labelledby="crearMenuModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="crearMenuModalLabel">Crear Menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form action="{{ route('menus.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="modulo" class="form-label">¿Es módulo Dinámico?</label>
                        <select name="modulo" id="modulo" class="form-select @error('modulo') is-invalid @enderror">
                            <option value="" selected disabled>Selecciona una sección</option>

                            <option value="1" {{ old('modulo') ? 'selected' : '' }}>Sí
                            </option>
                            <option value="0" {{ !(old('modulo')) ? 'selected' : '' }}>No
                            </option>
                        </select>
                        @error('modulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="contenedorModuloPadre" style="display: none;">
                        <label for="modulo_id" class="form-label">Seleccione un Módulo</label>
                        <select name="modulo_id" id="modulo_id"
                            class="form-select @error('modulo_id') is-invalid @enderror">
                            <option value="" selected disabled>Selecciona un módulo</option>
                            @foreach($modulos as $modulo)
                                <option value="{{ $modulo->id }}" {{ old('modulo_id') == $modulo->id ? 'selected' : '' }}>
                                    {{ $modulo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('modulo_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectModulo = document.getElementById('modulo');
        const selectRuta = document.getElementById('ruta');

        function actualizarRuta() {
            if (selectModulo.value === "1") {
                // Seleccionar modulo.index
                const opcionModulo = Array.from(selectRuta.options).find(opt => opt.value === "modulo.index");
                if (opcionModulo) opcionModulo.selected = true;

                // Bloquear cambios visualmente sin deshabilitar
                selectRuta.style.pointerEvents = "none";
                selectRuta.style.backgroundColor = "#e9ecef"; // simula disabled
            } else {
                selectRuta.style.pointerEvents = "auto";
                selectRuta.style.backgroundColor = ""; // restaurar
            }
        }


        // Ejecutar al cargar la página (para old value)
        actualizarRuta();

        // Ejecutar cada vez que cambie el select de módulo
        selectModulo.addEventListener('change', actualizarRuta);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectModulo = document.getElementById('modulo');
        const contenedorModuloPadre = document.getElementById('contenedorModuloPadre');

        function MostrarModulo() {
            if (selectModulo.value === "1") { // Si selecciona "Sí"
                contenedorModuloPadre.style.display = "block";
            } else {
                contenedorModuloPadre.style.display = "none";
                // Limpiar selección si se oculta
                document.getElementById('modulo_id').selectedIndex = 0;
            }
        }

        // Ejecutar al cargar la página (para mantener old value)
        MostrarModulo();

        // Ejecutar al cambiar selección
        selectModulo.addEventListener('change', MostrarModulo);
    });
</script>