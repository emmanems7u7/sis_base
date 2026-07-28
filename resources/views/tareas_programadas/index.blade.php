@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">

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
        <div class="card-body">


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>
                                Nombre
                            </th>

                            <th>
                                Programación
                            </th>

                            <th>
                                Próxima hora
                            </th>

                            <th>
                                Vigencia
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Ejecuciones
                            </th>

                            <th>
                                Última ejecución
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>
                    </thead>


                    <tbody>


                        @forelse($tareas as $tarea)
                            @php

                                $programacion = $tarea->parametros['programacion'] ?? [];

                                $diasNombre = [
                                    '1' => 'Lun',
                                    '2' => 'Mar',
                                    '3' => 'Mié',
                                    '4' => 'Jue',
                                    '5' => 'Vie',
                                    '6' => 'Sáb',
                                    '0' => 'Dom',
                                ];

                            @endphp


                            <tr>


                                {{-- Nombre --}}
                                <td>

                                    <strong>
                                        {{ $tarea->nombre }}
                                    </strong>

                                </td>



                                {{-- Tipo programación --}}
                                <td>


                                    @switch($programacion['tipo'] ?? null)
                                        @case('once')
                                            <span class="badge bg-primary">
                                                Única vez
                                            </span>

                                            <br>

                                            <small>
                                                {{ $programacion['fecha'] ?? '' }}
                                            </small>
                                        @break

                                        @case('daily')
                                            <span class="badge bg-info">
                                                Diario
                                            </span>
                                        @break

                                        @case('weekly')
                                            <span class="badge bg-warning text-dark">
                                                Semanal
                                            </span>

                                            <br>

                                            <small>

                                                @foreach ($programacion['dias'] ?? [] as $dia)
                                                    {{ $diasNombre[$dia] ?? '' }}

                                                    @if (!$loop->last)
                                                        ,
                                                    @endif
                                                @endforeach

                                            </small>
                                        @break

                                        @case('monthly')
                                            <span class="badge bg-secondary">
                                                Mensual
                                            </span>

                                            <br>

                                            <small>
                                                Día {{ $programacion['dia_mes'] }}
                                            </small>
                                        @break

                                        @case('interval')
                                            <span class="badge bg-dark">

                                                Cada
                                                {{ $programacion['cada'] }}

                                                @if ($programacion['unidad'] == 'minutes')
                                                    minutos
                                                @else
                                                    horas
                                                @endif

                                            </span>
                                        @break

                                        @default
                                            <span class="text-muted">
                                                Sin configuración
                                            </span>
                                    @endswitch


                                </td>




                                {{-- Hora --}}
                                <td>

                                    <i class="fas fa-clock me-1"></i>

                                    {{ $programacion['hora'] ?? '--:--' }}

                                </td>



                                {{-- Vigencia --}}
                                <td>


                                    <small>

                                        Desde:

                                        <strong>
                                            {{ $programacion['inicio'] ?? '-' }}
                                        </strong>


                                        <br>


                                        Hasta:

                                        <strong>
                                            {{ $programacion['fin'] ?? 'Sin límite' }}
                                        </strong>


                                    </small>


                                </td>



                                {{-- Estado --}}
                                <td>


                                    @if ($tarea->activo)
                                        <span class="badge bg-success">
                                            Activa
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Inactiva
                                        </span>
                                    @endif


                                </td>



                                {{-- Cantidad ejecuciones --}}
                                <td>


                                    <span class="badge bg-primary">

                                        {{ $tarea->ejecuciones_count ?? 0 }}

                                    </span>


                                </td>




                                {{-- Ultima ejecución --}}
                                <td>


                                    @if (isset($tarea->ultima_ejecucion))
                                        <small>

                                            {{ $tarea->ultima_ejecucion }}

                                        </small>
                                    @else
                                        <span class="text-muted">

                                            Nunca ejecutada

                                        </span>
                                    @endif


                                </td>




                                {{-- Acciones --}}
                                <td>

                                    <button class="btn btn-sm btn-info btn-historial" data-id="{{ $tarea->id }}"
                                        title="Ver historial">

                                        <i class="fas fa-history"></i>

                                    </button>

                                </td>


                            </tr>


                            @empty


                                <tr>

                                    <td colspan="8" class="text-center text-muted">

                                        No existen tareas programadas

                                    </td>

                                </tr>
                            @endforelse


                        </tbody>


                    </table>


                </div>

            </div>


            <div class="modal fade" id="modalHistorial" tabindex="-1">

                <div class="modal-dialog modal-xl">

                    <div class="modal-content">


                        <div class="modal-header">

                            <h5 class="modal-title">

                                <i class="fas fa-history me-2"></i>

                                Historial de ejecuciones

                            </h5>


                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                            </button>

                        </div>



                        <div class="modal-body">


                            <div class="table-responsive">


                                <table class="table table-hover align-middle">


                                    <thead>

                                        <tr>

                                            <th>
                                                Inicio
                                            </th>


                                            <th>
                                                Fin
                                            </th>


                                            <th>
                                                Estado
                                            </th>


                                            <th>
                                                Registros afectados
                                            </th>


                                            <th>
                                                Mensaje
                                            </th>


                                            <th>
                                                Error
                                            </th>


                                        </tr>


                                    </thead>



                                    <tbody id="tabla-historial">


                                    </tbody>


                                </table>


                            </div>


                        </div>


                    </div>

                </div>

            </div>

            <script>
                document.querySelectorAll('.btn-historial')
                    .forEach(btn => {


                        btn.addEventListener('click', function() {


                            let id = this.dataset.id;


                            cargarHistorial(id);


                        });


                    });




                async function cargarHistorial(id) {


                    const modal = new bootstrap.Modal(
                        document.getElementById('modalHistorial')
                    );


                    modal.show();



                    const tabla = document.getElementById('tabla-historial');


                    tabla.innerHTML = `

        <tr>

            <td colspan="6" class="text-center">

                <i class="fas fa-spinner fa-spin"></i>
                Cargando...

            </td>

        </tr>

    `;



                    try {


                        let response = await fetch(`/tareas/${id}/historial`);


                        let data = await response.json();



                        tabla.innerHTML = "";



                        if (data.length === 0) {

                            tabla.innerHTML = `

            <tr>

                <td colspan="6" class="text-center text-muted">

                    No existen ejecuciones registradas

                </td>

            </tr>

            `;


                            return;

                        }




                        data.forEach(item => {


                            tabla.innerHTML += `

            <tr>


                <td>

                    ${item.inicio ?? '-'}

                </td>



                <td>

                    ${item.fin ?? '-'}

                </td>



                <td>


                    ${
                        item.estado === 'success'

                        ?

                        `<span class="badge bg-success">
                                                    Correcto
                                                </span>`

                        :

                        `<span class="badge bg-danger">
                                                    Error
                                                </span>`
                    }


                </td>



                <td>

                    <span class="badge bg-info">

                        ${item.registros_afectados ?? 0}

                    </span>


                </td>



                <td>

                    ${item.mensaje ?? '-'}

                </td>



                <td>

                    ${
                        item.error
                        ?
                        `<small class="text-danger">
                                                    ${item.error}
                                                </small>`
                        :
                        '-'
                    }

                </td>



            </tr>


            `;


                        });



                    } catch (e) {

                        tabla.innerHTML = `

        <tr>

            <td colspan="6" class="text-center text-danger">

                Error cargando historial

            </td>

        </tr>

        `;

                    }


                }
            </script>
        @endsection
