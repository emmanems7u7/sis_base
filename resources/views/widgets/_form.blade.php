@csrf
<div class="mb-3">
    <label for="nombre" class="form-label">Nombre del Widget</label>
    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $widget->nombre ?? '') }}"
        required>
</div>

<div class="mb-3">
    <label for="modulo_id" class="form-label">Módulo</label>
    <select name="modulo_id" id="modulo_id" class="form-select">
        <option value="">-- Seleccionar módulo --</option>
        @foreach($modulos as $modulo)
            <option value="{{ $modulo->id }}" {{ (old('modulo_id', $widget->modulo_id ?? '') == $modulo->id) ? 'selected' : '' }}>
                {{ $modulo->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="formulario_id" class="form-label">Formulario asociado</label>
    <select name="formulario_id" id="formulario_id" class="form-select" disabled>
        <option value="">-- Seleccionar formulario --</option>
        @if(isset($widget) && $widget->modulo_id)
            @foreach($widget->modulo->formularios()->wherePivot('activo', 1)->get() as $form)
                <option value="{{ $form->id }}" {{ (old('formulario_id', $widget->formulario_id ?? '') == $form->id) ? 'selected' : '' }}>
                    {{ $form->nombre }}
                </option>
            @endforeach
        @endif
    </select>
</div>


<div class="mb-3">
    <label for="tipo" class="form-label">Tipo de Widget</label>
    <select name="tipo" id="tipo" class="form-select" required>
        <option value="">-- Seleccionar tipo --</option>
        @foreach($catalogos as $catalogo)
            <option value="{{ $catalogo->catalogo_codigo }}" {{ (old('tipo', $widget->tipo ?? '') == $catalogo->catalogo_codigo) ? 'selected' : '' }}>
                {{ $catalogo->catalogo_descripcion }}
            </option>
        @endforeach
    </select>
</div>

<div id="configuracion-container">
    @php
        $config = json_decode(old('configuracion', $widget->configuracion ?? '{}'), true);
    @endphp

    {{-- Botón --}}
    <div id="config-boton" class="tipo-config" style="display: none;">
        <h6>Configuración Botón</h6>
        <div class="mb-3">
            <label for="boton_texto" class="form-label">Texto del Botón</label>
            <input type="text" name="configuracion[texto]" id="boton_texto" class="form-control"
                value="{{ $config['texto'] ?? '' }}">
        </div>
        <div class="mb-3">
            <label for="boton_color" class="form-label">Color del Botón</label>
            <input type="color" name="configuracion[color]" id="boton_color" class="form-control form-control-color"
                value="{{ $config['color'] ?? '#0d6efd' }}">
        </div>
        <div class="mb-3">
            <label for="boton_link" class="form-label">URL del Botón</label>
            <input type="text" name="configuracion[url]" id="boton_link" class="form-control"
                value="{{ $config['url'] ?? '' }}">
        </div>
    </div>

    {{-- Contador --}}
    <div id="config-contador" class="tipo-config" style="display: none;">
        <h6>Configuración Contador</h6>
        <div class="mb-3">
            <label for="contador_valor" class="form-label">Valor Inicial</label>
            <input type="number" name="configuracion[valor]" id="contador_valor" class="form-control"
                value="{{ $config['valor'] ?? 0 }}">
        </div>
        <div class="mb-3">
            <label for="contador_color" class="form-label">Color del Contador</label>
            <input type="color" name="configuracion[color]" id="contador_color" class="form-control form-control-color"
                value="{{ $config['color'] ?? '#0d6efd' }}">
        </div>
        <div class="mb-3">
            <label for="contador_icono" class="form-label">Icono (FontAwesome)</label>
            <input type="text" name="configuracion[icono]" id="contador_icono" class="form-control"
                value="{{ $config['icono'] ?? 'fa-solid fa-hashtag' }}">
        </div>
    </div>

    <div id="config-estadistica" class="tipo-config" style="display:none;">

        <h6>Configuración Estadística</h6>


        {{-- Campo --}}

        <select id="campo_estadistica" name="configuracion[campo_id]" class="form-select mb-2">
            <option value="">-- Seleccionar campo --</option>
        </select>


        {{-- Tipo --}}
        <select name="configuracion[tipo_estadistica]" class="form-select mb-2">
            <option value="total">Total</option>
            <option value="conteo">Conteo</option>
            <option value="suma">Suma</option>
            <option value="promedio">Promedio</option>
        </select>

        {{-- Filtro por valor --}}
        <select id="campo_filtro" name="configuracion[filtros][campo][cf_id]" class="form-select mb-2">
            <option value="">-- Campo filtro --</option>
        </select>

        <input name="configuracion[filtros][campo][valor]" class="form-control mb-2" placeholder="Valor a filtrar">

        {{-- Periodo --}}
        <select name="configuracion[filtros][fecha]" class="form-select mb-2">
            <option value="hoy">Hoy</option>
            <option value="mes_actual">Mes actual</option>
            <option value="anio_actual">Año actual</option>
        </select>

    </div>
</div>

<button type="submit" class="btn btn-primary">Guardar Widget</button>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const bloques = document.querySelectorAll('.tipo-config');

        function ocultarTodo() {
            bloques.forEach(block => {
                block.style.display = 'none';

                // deshabilitar TODOS los inputs internos
                block.querySelectorAll('input, select, textarea').forEach(el => {
                    el.disabled = true;
                });
            });
        }

        function activarBloque(id) {
            const bloque = document.getElementById(id);
            if (!bloque) return;

            bloque.style.display = 'block';

            // habilitar SOLO los inputs visibles
            bloque.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = false;
            });
        }

        function mostrarConfiguracion() {
            const valor = tipoSelect.value;

            ocultarTodo();

            switch (valor) {
                case 'WID-001': // Botón
                    activarBloque('config-boton');
                    break;

                case 'WID-002': // Estadística
                    activarBloque('config-estadistica');
                    break;

                case 'WID-010': // Contador
                    activarBloque('config-contador');
                    break;

                case 'WID-020': // Gráfico barras
                    activarBloque('config-grafico-barra');
                    break;

                case 'WID-021': // Gráfico línea
                    activarBloque('config-grafico-linea');
                    break;
            }
        }

        tipoSelect.addEventListener('change', mostrarConfiguracion);

        // ejecutar al cargar (modo editar)
        mostrarConfiguracion();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moduloSelect = document.getElementById('modulo_id');
        const formularioSelect = document.getElementById('formulario_id');

        moduloSelect.addEventListener('change', function () {
            const moduloId = this.value;

            // Reiniciar formulario
            formularioSelect.innerHTML = '<option value="">-- Seleccionar formulario --</option>';
            formularioSelect.disabled = true;

            if (!moduloId) return;

            fetch(`/modulo/${moduloId}/formularios`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f.id;
                        opt.textContent = f.nombre;
                        formularioSelect.appendChild(opt);
                    });
                    formularioSelect.disabled = false;
                })
                .catch(err => console.error(err));
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const formularioSelect = document.getElementById('formulario_id');
        const campoEstadistica = document.getElementById('campo_estadistica');
        const campoFiltro = document.getElementById('campo_filtro');

        function resetCampos() {
            campoEstadistica.innerHTML = '<option value="">-- Seleccionar campo --</option>';
            campoFiltro.innerHTML = '<option value="">-- Campo filtro --</option>';
        }

        formularioSelect.addEventListener('change', function () {

            const formularioId = this.value;
            resetCampos();

            if (!formularioId) return;

            fetch(`/formulario/${formularioId}/campos`)
                .then(res => res.json())
                .then(data => {
                    const campos = data.campos ?? [];
                    campos.forEach(campo => {

                        // campo principal
                        const opt1 = document.createElement('option');
                        opt1.value = campo.id;
                        opt1.textContent = campo.etiqueta;
                        campoEstadistica.appendChild(opt1);

                        // campo filtro
                        const opt2 = document.createElement('option');
                        opt2.value = campo.id;
                        opt2.textContent = campo.etiqueta;
                        campoFiltro.appendChild(opt2);
                    });
                })
                .catch(err => console.error(err));
        });

    });
</script>