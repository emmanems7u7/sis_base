@php
    $configuracion = [];

    if (isset($consulta) && !empty($consulta->configuracion)) {
        $configuracion = $consulta->configuracion;
    }

@endphp

<input type="hidden" id="configuracion" name="configuracion">

<div class="row">

    <div class="col-md-3">

        <div class="card">

            <div class="card-header">
                Formulario
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label>
                        Nombre Consulta
                    </label>

                    <input type="text" name="nombre" class="form-control" required
                        value="{{ old('nombre', $consulta->nombre ?? '') }}">

                </div>

                <div class="mb-3">

                    <label>
                        Formulario
                    </label>

                    <select name="formulario_id" id="formulario" class="form-select" required>

                        <option value="">
                            Seleccione
                        </option>

                        @foreach ($formularios as $formulario)
                            <option value="{{ $formulario->id }}" @selected(old('formulario_id', $consulta->formulario_id ?? '') == $formulario->id)>
                                {{ $formulario->nombre }}
                            </option>
                        @endforeach

                    </select>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card">

            <div class="card-header">
                Campos
            </div>

            <div class="card-body">

                <div id="campos-container">

                    Seleccione formulario

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-5">

        <div class="card">

            <div class="card-header d-flex justify-content-between">

                <span>Filtros</span>

                <button type="button" class="btn btn-sm btn-primary" id="addFiltro">
                    Agregar
                </button>

            </div>

            <div class="card-body" id="filtros-container">

            </div>

        </div>

    </div>

</div>

<div class="card mt-3">

    <div class="card-header">
        Ordenamiento
    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <select id="orderField" class="form-select">

                </select>

            </div>

            <div class="col-md-3">

                <select id="orderDirection" class="form-select">

                    <option value="asc">
                        Ascendente
                    </option>

                    <option value="desc">
                        Descendente
                    </option>

                </select>

            </div>

        </div>

    </div>


</div>

<script>
    let camposGlobal = [];

    let consultaConfig = @json($configuracion);

    document.addEventListener('DOMContentLoaded', () => {

        const formulario =
            document.getElementById('formulario');

        if (formulario.value) {

            formulario.dispatchEvent(
                new Event('change')
            );

        }

    });

    document
        .getElementById('formulario')
        .addEventListener('change', async function() {

            const formularioId = this.value;

            if (!formularioId) {

                document
                    .getElementById('campos-container')
                    .innerHTML = 'Seleccione formulario';

                return;
            }

            const response =
                await fetch(
                    `/formularios/${formularioId}/campos`
                );

            const campos =
                await response.json();

            camposGlobal = campos;

            renderCampos();

            aplicarConfiguracion();

        });

    function renderCampos() {

        let html = '';

        camposGlobal.forEach(campo => {

            const margen =
                (campo.nivel || 0) * 20;

            if (campo.es_relacion) {

                html += `
                    <div
                        class="mt-2 mb-1 border-start border-3 border-primary ps-2"
                        style="margin-left:${margen}px">

                        <strong class="text-primary">

                            <i class="fas fa-folder-open"></i>

                            ${campo.etiqueta}

                        </strong>

                    </div>
                `;

                return;
            }

            html += `
                <div
                    class="form-check"
                    style="margin-left:${margen}px">

                    <input
                        class="form-check-input campo-select"
                        type="checkbox"
                        value="${campo.id}"
                    >

                    <label class="form-check-label">

                        ${campo.etiqueta_simple}

                    </label>

                </div>
            `;
        });

        document
            .getElementById('campos-container')
            .innerHTML = html;

        actualizarOrder();
    }

    function actualizarOrder() {

        let html = '';

        camposGlobal.forEach(campo => {

            // Ignorar hijos y nietos
            if (campo.nivel > 0) {
                return;
            }

            html += `
        <option value="${campo.id}">
            ${campo.etiqueta}
        </option>
    `;
        });

        document
            .getElementById('orderField')
            .innerHTML = html;
    }

    function aplicarConfiguracion() {

        if (!consultaConfig) {
            return;
        }

        document.querySelectorAll('.campo-select').forEach(item => {

            item.checked = false;

        });

        (consultaConfig.select || []).forEach(id => {

            const checkbox =
                document.querySelector(
                    `.campo-select[value="${id}"]`
                );

            if (checkbox) {

                checkbox.checked = true;

            }

        });

        if (consultaConfig.orderBy && consultaConfig.orderBy.length) {

            document.getElementById('orderField').value = consultaConfig.orderBy[0].campo;

            document.getElementById('orderDirection').value = consultaConfig.orderBy[0].direccion;

        }

        const filtrosContainer = document.getElementById('filtros-container');

        filtrosContainer.innerHTML = '';

        (consultaConfig.where || []).forEach(filtro => {

            agregarFiltro(filtro);

        });

    }

    function agregarFiltro(data = null) {

        let opciones = '';

        camposGlobal.forEach(campo => {

            if (campo.es_relacion) {
                return;
            }

            opciones += `
                <option value="${campo.id}">
                    ${campo.etiqueta}
                </option>
            `;
        });

        document
            .getElementById('filtros-container')
            .insertAdjacentHTML(
                'beforeend',
                `
                <div class="row mb-2 filtro">

                    <div class="col-md-4">

                        <select
                            class="form-select campo"
                        >

                            ${opciones}

                        </select>

                    </div>

                    <div class="col-md-3">

                        <select
                            class="form-select operador"
                        >

                            <option value="=">=</option>

                            <option value="!=">!=</option>

                            <option value=">">></option>

                            <option value="<"><</option>

                            <option value="like">
                                Contiene
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <input
                            type="text"
                            class="form-control valor"
                        >

                    </div>

                    <div class="col-md-1">

                        <button
                            type="button"
                            class="btn btn-danger btn-sm removeFiltro"
                        >
                            X
                        </button>

                    </div>

                </div>
                `
            );

        const fila =
            document.querySelector(
                '#filtros-container .filtro:last-child'
            );

        if (data) {

            fila.querySelector('.campo').value =
                data.campo;

            fila.querySelector('.operador').value =
                data.operador;

            fila.querySelector('.valor').value =
                data.valor;

        }

    }

    document
        .getElementById('addFiltro')
        .addEventListener('click', () => {

            agregarFiltro();

        });

    document.addEventListener('click', function(e) {

        if (
            e.target.classList.contains(
                'removeFiltro'
            )
        ) {

            e.target
                .closest('.filtro')
                .remove();

        }

    });

    document
        .getElementById('formConsulta')
        .addEventListener('submit', function() {

            const select = [];

            document
                .querySelectorAll(
                    '.campo-select:checked'
                )
                .forEach(item => {

                    select.push(
                        item.value
                    );

                });

            const where = [];

            document
                .querySelectorAll('.filtro')
                .forEach(item => {

                    where.push({

                        campo: item.querySelector('.campo').value,

                        operador: item.querySelector('.operador').value,

                        valor: item.querySelector('.valor').value

                    });

                });

            const config = {

                select,

                where,

                orderBy: [

                    {
                        campo: document
                            .getElementById('orderField')
                            .value,

                        direccion: document
                            .getElementById('orderDirection')
                            .value
                    }

                ]

            };

            document
                .getElementById('configuracion')
                .value =
                JSON.stringify(config);

        });
</script>
