<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $export }}</title>
</head>

<body>

    <table>
        <tr>
            <td colspan="{{ count($respuestas[0] ?? []) }}">
                <strong>{{ $export }}</strong>
            </td>
        </tr>

        <tr>
            <td colspan="{{ count($respuestas[0] ?? []) }}">
                {{ $formulario->descripcion }}
            </td>
        </tr>

        <tr>
            <td>
                Generado por:
            </td>
            <td>
                {{ $user->nombre_completo }}
            </td>
        </tr>

        <tr>
            <td>
                Fecha:
            </td>
            <td>
                {{ $fecha }}
            </td>
        </tr>
    </table>

    <br>

    @if(count($respuestas) > 0)

        <table border="1">

            <thead>
                <tr>
                    @foreach(array_keys($respuestas[0]) as $columna)
                        <th>
                            {{ $columna }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach($respuestas as $fila)
                    <tr>

                        @foreach($fila as $valor)

                            <td>
                                {{ is_array($valor) ? json_encode($valor) : strip_tags($valor) }}
                            </td>

                        @endforeach

                    </tr>
                @endforeach
            </tbody>

        </table>

    @else

        <table border="1">
            <tr>
                <td>No hay respuestas registradas.</td>
            </tr>
        </table>

    @endif

</body>

</html>