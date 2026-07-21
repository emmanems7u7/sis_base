@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h4 class="mb-0">
                        Reporte: {{ $consulta->nombre }}
                    </h4>

                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">

        <div class="card-header">
            Filtros
        </div>

        <div class="card-body">

            <div class="row">
                @foreach ($filtros['where'] ?? [] as $filtro)
                    @php
                        $nombreCampo = $filtro['etiqueta'] ?? $filtro['campo'];
                    @endphp


                    @switch($filtro['tipo_campo'])
                        {{-- TEXT --}}
                        @case('CAMPF-012')
                            @include('consultas.componentes.text')
                        @break

                        {{-- NUMBER --}}
                        @case('CAMPF-013')
                            @include('consultas.componentes.number')
                        @break

                        {{-- CHECKBOX --}}
                        @case('CAMPF-015')
                            @include('consultas.componentes.checkbox')
                        @break

                        {{-- RADIO --}}
                        @case('CAMPF-016')
                            @include('consultas.componentes.radio')
                        @break

                        {{-- SELECTOR --}}
                        @case('CAMPF-017')
                            @include('consultas.componentes.selector')
                        @break

                        {{-- FECHA --}}
                        @case('CAMPF-021')
                            @include('consultas.componentes.fecha')
                        @break

                        {{-- HORA --}}
                        @case('CAMPF-022')
                            @include('consultas.componentes.hora')
                        @break
                    @endswitch
                @endforeach

            </div>

            <button type="button" id="btnEjecutarConsulta" class="btn btn-success">

                <i class="fas fa-search"></i>
                Generar Reporte

            </button>

        </div>

    </div>


    <div class="card mt-3 shadow-lg">

        <div class="card-header">
            Resultado
        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered" id="tablaResultado">

                    <thead>

                    </thead>


                    <tbody>

                    </tbody>


                </table>

            </div>

        </div>

    </div>
    <script>
        document.getElementById('btnEjecutarConsulta').addEventListener('click', () => {


            let filtros = {};


            document.querySelectorAll('.filtro-input').forEach(input => {

                const valor = input.value;

                if (valor === '') {
                    return;
                }

                const match = input.name.match(/^filtros\[(.*?)\](?:\[(.*?)\])?$/);

                if (!match) {
                    return;
                }

                const campo = match[1];
                const subcampo = match[2];

                if (subcampo) {

                    filtros[campo] ??= {};
                    filtros[campo][subcampo] = valor;

                } else {

                    filtros[campo] = valor;

                }

            });

            fetch("{{ route('consultas.ejecutar', $consulta->id) }}", {


                    method: 'POST',

                    headers: {


                        'Content-Type': 'application/json',

                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .content

                    },


                    body: JSON.stringify({

                        filtros

                    })


                })
                .then(r => r.json())
                .then(data => {


                    renderTabla(data);


                });


        });


        function renderTabla(data) {


            let tabla =
                document.getElementById('tablaResultado');


            let thead =
                tabla.querySelector('thead');


            let tbody =
                tabla.querySelector('tbody');



            thead.innerHTML = '';
            tbody.innerHTML = '';



            /*
            |--------------------------------------------------------------------------
            | Cabecera
            |--------------------------------------------------------------------------
            */

            let tr =
                document.createElement('tr');


            Object.entries(data.columnas).forEach(([campo, etiqueta]) => {

                let th = document.createElement('th');

                th.innerText = etiqueta;

                tr.appendChild(th);

            });


            thead.appendChild(tr);



            /*
            |--------------------------------------------------------------------------
            | Datos
            |--------------------------------------------------------------------------
            */

            data.datos.forEach(row => {

                let tr = document.createElement('tr');

                Object.keys(data.columnas).forEach(campo => {

                    let td = document.createElement('td');

                    td.innerText = row[campo] ?? '';

                    tr.appendChild(td);

                });

                tbody.appendChild(tr);

            });


        }
    </script>
@endsection
