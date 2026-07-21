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

                    @foreach ($campos as $campo)
                        @if ($campo['es_relacion'])
                            <div class="mt-2 mb-1 border-start border-3 border-primary ps-2"
                                style="margin-left: {{ ($campo['nivel'] ?? 0) * 20 }}px">

                                <strong class="text-primary">
                                    <i class="fas fa-folder-open"></i>
                                    {{ $campo['etiqueta'] }}
                                </strong>

                            </div>
                        @else
                            <div class="form-check" style="margin-left: {{ ($campo['nivel'] ?? 0) * 20 }}px">

                                <input class="form-check-input campo-select" type="checkbox"
                                    value="{{ $campo['id'] }}">

                                <label class="form-check-label">
                                    {{ $campo['etiqueta_simple'] }}
                                </label>

                            </div>
                        @endif
                    @endforeach

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-5">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <strong>Filtros del reporte</strong>

                <button type="button" id="btnAgregarFiltro" class="btn btn-primary btn-sm">

                    <i class="fas fa-plus"></i>

                    Agregar filtro

                </button>

            </div>

            <div class="card-body" id="contenedorFiltros">

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
                        @foreach ($campos as $campo)
                            @continue($campo['nivel'] > 0)

                            <option value="{{ $campo['id'] }}">
                                {{ $campo['etiqueta'] }}
                            </option>
                        @endforeach
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
        const filtrosDisponibles = {

            // CAMPF-012 -> Text
            "CAMPF-012": [{
                    valor: "contiene",
                    texto: "Contiene"
                },
                {
                    valor: "igual",
                    texto: "Igual"
                },
                {
                    valor: "empieza",
                    texto: "Empieza con"
                },
                {
                    valor: "termina",
                    texto: "Termina con"
                },
                {
                    valor: "vacio",
                    texto: "Vacío"
                },
                {
                    valor: "novacio",
                    texto: "No vacío"
                }
            ],

            // CAMPF-013 -> Number
            "CAMPF-013": [{
                    valor: "igual",
                    texto: "Igual"
                },
                {
                    valor: "mayor",
                    texto: "Mayor que"
                },
                {
                    valor: "menor",
                    texto: "Menor que"
                },
                {
                    valor: "rango",
                    texto: "Desde / Hasta"
                }
            ],

            // CAMPF-028 -> Campo autocompletado
            "CAMPF-028": [{
                    valor: "contiene",
                    texto: "Contiene"
                },
                {
                    valor: "igual",
                    texto: "Igual"
                }
            ],

            // Selector
            "CAMPF-017": [{
                    valor: "igual",
                    texto: "Igual"
                },
                {
                    valor: "multiple",
                    texto: "Selección múltiple"
                }
            ],

            // Checkbox
            "CAMPF-015": [{
                valor: "contiene",
                texto: "Contiene"
            }],

            // Radio
            "CAMPF-016": [{
                valor: "igual",
                texto: "Igual"
            }],

            // Fecha
            "CAMPF-021": [{
                    valor: "igual",
                    texto: "Fecha exacta"
                },

                {
                    valor: "rango",
                    texto: "Desde / Hasta"
                },
                {
                    valor: "hoy",
                    texto: "Hoy"
                },
                {
                    valor: "ayer",
                    texto: "Ayer"
                },
                {
                    valor: "7dias",
                    texto: "Últimos 7 días"
                },
                {
                    valor: "30dias",
                    texto: "Últimos 30 días"
                }
            ],

            // Hora
            "CAMPF-022": [{
                    valor: "igual",
                    texto: "Hora exacta"
                },
                {
                    valor: "desde",
                    texto: "Desde"
                },
                {
                    valor: "hasta",
                    texto: "Hasta"
                }
            ],

            "BOOLEAN": [{
                    valor: "si",
                    texto: "Sí"
                },
                {
                    valor: "no",
                    texto: "No"
                }
            ]

        };
        const camposGlobal = @json($campos);

        function agregarFiltro_(data = null) {

            let opcionesCampos = '';

            camposGlobal.forEach(campo => {

                if (campo.es_relacion)
                    return;

                opcionesCampos += `
        <option
            value="${campo.id}"
            data-tipo="${campo.tipo}">

            ${campo.etiqueta}

        </option>
    `;

            });

            document
                .getElementById('contenedorFiltros')
                .insertAdjacentHTML(
                    'beforeend',
                    `
        <div class="row mb-3 filtro">

            <div class="col-md-5">

                <select class="form-select campoFiltro">

                    ${opcionesCampos}

                </select>

            </div>

            <div class="col-md-5">

                <select class="form-select tipoFiltro">

                </select>

            </div>

            <div class="col-md-2">

                <button
                    type="button"
                    class="btn btn-danger removeFiltro">

                    <i class="fas fa-trash"></i>

                </button>

            </div>

        </div>
        `
                );

            const fila = document.querySelector('#contenedorFiltros .filtro:last-child');

            cargarTiposFiltro(fila);

            if (data) {

                fila.querySelector('.campoFiltro').value = data.campo;

                cargarTiposFiltro(fila);

                fila.querySelector('.tipoFiltro').value = data.tipo;

            }

        }
        document.addEventListener('change', function(e) {

            if (e.target.classList.contains('campoFiltro')) {

                cargarTiposFiltro(
                    e.target.closest('.filtro')
                );

            }

        });

        function cargarTiposFiltro(fila) {

            const selectCampo = fila.querySelector('.campoFiltro');

            const tipo = selectCampo.options[
                selectCampo.selectedIndex
            ].dataset.tipo;

            let html = '';

            (filtrosDisponibles[tipo] || []).forEach(item => {

                html += `
        <option value="${item.valor}">
            ${item.texto}
        </option>
    `;

            });

            fila.querySelector('.tipoFiltro').innerHTML = html;

        }

        document
            .getElementById('btnAgregarFiltro')
            .addEventListener('click', () => {

                agregarFiltro_();

            });
        document.addEventListener('click', function(e) {

            if (e.target.closest('.removeFiltro')) {

                e.target
                    .closest('.filtro')
                    .remove();

            }

        });

        const filtros = [];

        document
            .querySelectorAll('#contenedorFiltros .filtro')
            .forEach(fila => {

                filtros.push({

                    campo: fila.querySelector('.campoFiltro').value,

                    tipo: fila.querySelector('.tipoFiltro').value

                });

            });
    </script>



    <script>
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


        const contenedor = document.getElementById('contenedorFiltros');

        contenedor.innerHTML = '';

        (consultaConfig.where || []).forEach(filtro => {

            agregarFiltro_(filtro);

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
                    .forEach(fila => {

                        const campoSelect = fila.querySelector('.campoFiltro');

                        const opcionSeleccionada =
                            campoSelect.options[campoSelect.selectedIndex];


                        where.push({

                            campo: campoSelect.value,

                            tipo_campo: opcionSeleccionada.dataset.tipo,

                            tipo: fila.querySelector('.tipoFiltro').value

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
