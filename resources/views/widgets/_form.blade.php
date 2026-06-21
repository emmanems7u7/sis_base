@csrf
<div class="mb-3">
    <label for="nombre" class="form-label">Nombre del Widget</label>
    <input type="text" name="nombre" id="nombre" class="form-control"
        value="{{ old('nombre', $widget->nombre ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="formulario_id" class="form-label">Formulario</label>
    <select name="formulario_id" id="formulario_id" class="form-select">
        <option value="">-- Seleccionar formulario --</option>

        @foreach ($formularios as $form)
            <option value="{{ $form->id }}"
                {{ old('formulario_id', $widget->formulario_id ?? '') == $form->id ? 'selected' : '' }}>
                {{ $form->nombre }}
            </option>
        @endforeach
    </select>
</div>


<div class="mb-3">
    <label for="tipo" class="form-label">Tipo de Widget</label>
    <select name="tipo" id="tipo" class="form-select" required>
        <option value="">-- Seleccionar tipo --</option>
        @foreach ($catalogos as $catalogo)
            <option value="{{ $catalogo->catalogo_codigo }}"
                {{ old('tipo', $widget->tipo ?? '') == $catalogo->catalogo_codigo ? 'selected' : '' }}>
                {{ $catalogo->catalogo_descripcion }}
            </option>
        @endforeach
    </select>
</div>

<div id="configuracion-container">
    @php
        $config = old('configuracion', $widget->configuracion ?? '{}');
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

    <div id="config-contador" class="tipo-config" style="display:none;">

        <h6 class="mb-3">Configuración Contador</h6>

        <div class="row">

            <div class="col-md-4 mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="configuracion[titulo]" class="form-control"
                    placeholder="Ej. Total de Ventas">
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label">Color</label>
                <input type="color" name="configuracion[color]" class="form-control form-control-color w-100"
                    value="#0d6efd">
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label">Período</label>
                <select name="configuracion[periodo]" class="form-select">
                    <option value="">Todos</option>
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta semana</option>
                    <option value="mes">Este mes</option>
                    <option value="anio">Este año</option>
                </select>
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label">Icono FontAwesome</label>
                <input type="text" name="configuracion[icono]" class="form-control" placeholder="fa-solid fa-users">
            </div>
            <div class="col-md-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="configuracion[mostrar_icono]" checked>
                    <label class="form-check-label">Mostrar icono</label>
                </div>
            </div>


            <div class="col-md-2 mb-3">
                <label class="form-label">Prefijo</label>
                <input type="text" name="configuracion[prefijo]" class="form-control" placeholder="Bs.">
            </div>

            <div class="col-md-2 mb-3">
                <label class="form-label">Sufijo</label>
                <input type="text" name="configuracion[sufijo]" class="form-control" placeholder="Usuarios">
            </div>


            <div class="col-12 mb-3">
                <label class="form-label">Descripción</label>
                <input type="text" name="configuracion[descripcion]" class="form-control"
                    placeholder="Texto descriptivo">
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Filtros</h6>

                <button type="button" class="btn btn-sm btn-primary" id="btn-agregar-filtro">
                    <i class="fas fa-plus"></i>
                    Agregar filtro
                </button>
            </div>

            <div id="contenedor-filtros">

                <div class="row filtro-item g-2 mb-2">

                    <div class="col-md-4">

                        <label class="form-label">Campo</label>

                        <select name="configuracion[filtros][0][campo_id]" class="form-select campo-dinamico">

                            <option value="">Seleccionar campo</option>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">Operador</label>

                        <select name="configuracion[filtros][0][operador]" class="form-select">

                            <option value="=">=</option>
                            <option value="!=">!=</option>
                            <option value=">">></option>
                            <option value="<">
                                << /option>
                            <option value=">=">>=</option>
                            <option value="<=">
                                <=< /option>
                            <option value="like">Contiene</option>
                            <option value="not_like">No contiene</option>

                        </select>

                    </div>

                    <div class="col-md-5">

                        <label class="form-label">Valor</label>

                        <input type="text" name="configuracion[filtros][0][valor]" class="form-control"
                            placeholder="Valor a buscar">

                    </div>

                    <div class="col-md-1 d-flex align-items-end">

                        <button type="button" class="btn btn-outline-danger btn-xs w-100 btn-eliminar-filtro">

                            <i class="fas fa-trash"></i>

                        </button>

                    </div>

                </div>

            </div>


            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="configuracion[mostrar_descripcion]"
                        checked>
                    <label class="form-check-label">Mostrar descripción</label>
                </div>
            </div>

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

    <div id="config-graficos" class="tipo-config" style="display:none;">

        <h6 id="titulo-grafico"></h6>

        <div id="campos-grafico"></div>

        <input type="text" name="configuracion[titulo]" class="form-control mb-2"
            placeholder="Título del gráfico">

    </div>

</div>

<button type="submit" class="btn btn-primary">Guardar Widget</button>


<script>
    let filtroIndex = 1;

    document.getElementById('btn-agregar-filtro')
        .addEventListener('click', function() {

            const html = `
            <div class="row filtro-item g-2 mb-2">

                <div class="col-md-4">
                    <select
                        name="configuracion[filtros][${filtroIndex}][campo_id]"
                        class="form-select campo-dinamico">
                        <option value="">Seleccionar campo</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select
                        name="configuracion[filtros][${filtroIndex}][operador]"
                        class="form-select">

                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value=">=">>=</option>
                        <option value="<="><=</option>
                        <option value="like">Contiene</option>
                        <option value="not_like">No contiene</option>

                    </select>
                </div>

                <div class="col-md-5">
                    <input
                        type="text"
                        name="configuracion[filtros][${filtroIndex}][valor]"
                        class="form-control"
                        placeholder="Valor a buscar">
                </div>

                <div class="col-md-1">
                    <button
                        type="button"
                        class="btn btn-outline-danger btn xs w-100 btn-eliminar-filtro">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

            </div>
        `;

            document
                .getElementById('contenedor-filtros')
                .insertAdjacentHTML('beforeend', html);

            filtroIndex++;
        });

    document.addEventListener('click', function(e) {

        if (e.target.closest('.btn-eliminar-filtro')) {

            e.target
                .closest('.filtro-item')
                .remove();
        }

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const moduloSelect = document.getElementById('modulo_id');
        const formularioSelect = document.getElementById('formulario_id');

        moduloSelect.addEventListener('change', function() {
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
    const configuracionesGraficos = {

        'WID-007': { // Línea
            titulo: 'Gráfico de Línea',

            campos: {
                x: true,
                y: true
            },

            apariencia: {
                titulo: true
            },

            datos: {
                periodo: true
            }
        },

        'WID-008': { // Barra
            titulo: 'Gráfico de Barra',

            campos: {
                x: true,
                y: true
            },

            apariencia: {
                titulo: true,
                color: true,
                mostrarLeyenda: true
            },

            datos: {
                tipo: true,
                ordenar: true,
                limite: true
            }
        },

        'WID-009': { // Pastel
            titulo: 'Gráfico Pastel',

            campos: {
                x: true,
                y: true
            },

            apariencia: {
                titulo: true,
                subtitulo: true,
                color: true,
                mostrarLeyenda: true,
                mostrarEtiquetas: true,
                altura: true,
                ancho: true,
                colorFondo: true,
                colorTexto: true,
                animacion: true
            },

            datos: {
                tipo: true
            }
        }

    };

    document.addEventListener('DOMContentLoaded', function() {
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

                case 'WID-007':
                case 'WID-008':
                case 'WID-009':
                    activarBloque('config-graficos');
                    renderizarGrafico(valor);
                    break;

            }
        }

        tipoSelect.addEventListener('change', mostrarConfiguracion);

        // ejecutar al cargar (modo editar)
        mostrarConfiguracion();
    });

    function renderizarGrafico(tipo) {

        const config = configuracionesGraficos[tipo];

        if (!config) return;

        document.getElementById('titulo-grafico').textContent = config.titulo;

        let html = '';

        if (config.campos?.x) {
            html +=
                `<label class="form-label">Campo X <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Campo para el eje horizontal o categorías."></i></label><select id="campo_x" name="configuracion[campo_x_id]" class="form-select mb-3 campo-dinamico" data-placeholder="-- Seleccionar campo X --"><option value="">-- Seleccionar campo X --</option></select>`;
        }

        if (config.campos?.y) {
            html +=
                `<label class="form-label">Campo Y <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Campo para valores numéricos."></i></label><select id="campo_y" name="configuracion[campo_y_id]" class="form-select mb-3 campo-dinamico" data-placeholder="-- Seleccionar campo Y --"><option value="">-- Seleccionar campo Y --</option></select>`;
        }

        if (config.datos?.tipo || config.datos?.periodo || config.datos?.ordenar || config.datos?.limite) {
            html += `<h6 class="mt-3 mb-2">Configuración de Datos</h6>`;
        }

        if (config.datos?.tipo) {
            html +=
                `<label class="form-label">Operación <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Operación aplicada sobre los datos."></i></label><select name="configuracion[tipo]" class="form-select mb-3"><option value="conteo">Conteo</option><option value="suma">Suma</option><option value="promedio">Promedio</option><option value="maximo">Máximo</option><option value="minimo">Mínimo</option></select>`;
        }

        if (config.datos?.periodo) {
            html +=
                `<label class="form-label">Período <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Agrupa los datos por período."></i></label><select name="configuracion[periodo]" class="form-select mb-3"><option value="">Sin período</option><option value="dia">Día</option><option value="semana">Semana</option><option value="mes">Mes</option><option value="anio">Año</option></select>`;
        }

        if (config.datos?.ordenar) {
            html +=
                `<label class="form-label">Ordenamiento <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Orden de visualización de los resultados."></i></label><select name="configuracion[orden]" class="form-select mb-3"><option value="asc">Ascendente</option><option value="desc">Descendente</option></select>`;
        }

        if (config.datos?.limite) {
            html +=
                `<label class="form-label">Límite de Registros <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Cantidad máxima de registros a mostrar."></i></label><input type="number" name="configuracion[limite]" class="form-control mb-3" value="10" min="1" placeholder="Ej: 10">`;
        }

        if (config.apariencia?.titulo || config.apariencia?.subtitulo || config.apariencia?.color || config.apariencia
            ?.mostrarLeyenda || config.apariencia?.mostrarEtiquetas) {
            html += `<h6 class="mt-3 mb-2">Apariencia</h6>`;
        }

        if (config.apariencia?.titulo) {
            html +=
                `<label class="form-label">Título <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Título principal del gráfico."></i></label><input type="text" name="configuracion[titulo]" class="form-control mb-3" placeholder="Ej: Ventas por Mes">`;
        }

        if (config.apariencia?.subtitulo) {
            html +=
                `<label class="form-label">Subtítulo <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Texto complementario debajo del título."></i></label><input type="text" name="configuracion[subtitulo]" class="form-control mb-3" placeholder="Ej: Gestión Comercial 2026">`;
        }

        if (config.apariencia?.color) {
            html +=
                `<label class="form-label">Color Principal <i class="fas fa-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="Color principal utilizado en el gráfico."></i></label><input type="color" name="configuracion[color]" class="form-control form-control-color mb-3" value="#0d6efd">`;
        }

        if (config.apariencia?.mostrarLeyenda) {
            html +=
                `<div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="configuracion[mostrar_leyenda]" checked><label class="form-check-label">Mostrar leyenda</label></div>`;
        }

        if (config.apariencia?.mostrarEtiquetas) {
            html +=
                `<div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="configuracion[mostrar_etiquetas]" checked><label class="form-check-label">Mostrar etiquetas</label></div>`;
        }

        document.getElementById('campos-grafico').innerHTML = html;

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        cargarCamposFormulario();
    }

    function cargarCamposFormulario() {

        const formularioId =
            document.getElementById('formulario_id').value;

        if (!formularioId) return;

        fetch(`/formulario/${formularioId}/campos`)
            .then(res => res.json())
            .then(data => {

                const campos = data.campos ?? [];

                getSelectsDinamicos().forEach(select => {

                    campos.forEach(campo => {

                        select.innerHTML += `
                    <option value="${campo.id}">
                        ${campo.etiqueta}
                    </option>
                `;

                    });

                });

            });
    }
</script>




<script>
    const formularioSelect = document.getElementById('formulario_id');

    function getSelectsDinamicos() {
        return document.querySelectorAll('.campo-dinamico');
    }

    const campoFiltro = document.getElementById('campo_filtro');

    function resetCampos() {

        getSelectsDinamicos().forEach(select => {

            select.innerHTML = `
        <option value="">
            ${select.dataset.placeholder}
        </option>
    `;

        });

    }

    formularioSelect.addEventListener('change', function() {

        const formularioId = this.value;
        resetCampos();

        if (!formularioId) return;

        fetch(`/formulario/${formularioId}/campos`)
            .then(res => res.json())
            .then(data => {
                const campos = data.campos ?? [];
                campos.forEach(campo => {

                    getSelectsDinamicos().forEach(select => {

                        const option = document.createElement('option');

                        option.value = campo.id;
                        option.textContent = campo.etiqueta;

                        select.appendChild(option);

                    });

                });
            })
            .catch(err => console.error(err));
    });
</script>
