@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h4 class="mb-0">
                        Constructor de Consultas
                    </h4>

                    <a href="{{ route('consultas.create', $formulario) }}" class="btn btn-primary">

                        <i class="fas fa-plus"></i>
                        Nueva Consulta

                    </a>
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
            <table class="table table-hover mb-0">

                <thead>

                    <tr>
                        <th>Nombre</th>
                        <th>Formulario</th>
                        <th width="180">Acciones</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($consultas as $consulta)
                        <tr>

                            <td>
                                {{ $consulta->nombre }}
                            </td>

                            <td>
                                {{ $consulta->formulario->nombre }}
                            </td>

                            <td>

                                <a href="{{ route('consultas.show', $consulta) }}" class="btn btn-info btn-xs">

                                    Ver

                                </a>
                                <a href="{{ route('consultas.edit', ['consulta' => $consulta, 'formulario' => $formulario]) }}"
                                    class="btn btn-warning btn-xs">

                                    Editar

                                </a>
                                <a href="{{ route('consultas.destroy', $consulta) }}" class="btn btn-danger btn-xs">

                                    Eliminar

                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="text-center">

                                Sin registros

                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>
    </div>
@endsection
