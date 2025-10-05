<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $export }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #2c3e50;
        }

        h1 {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .info-header {
            margin-bottom: 20px;
        }

        .info-header p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #e9ecef;
            font-weight: bold;
        }

        img {
            max-width: 80px;
            max-height: 80px;
            display: block;
            margin: 2px 0;
        }

        .no-data {
            font-style: italic;
            color: #6c757d;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
@php
    setlocale(LC_TIME, 'es_ES.UTF-8'); // Configura español
    $fechaLiteral = \Carbon\Carbon::parse($fecha)->translatedFormat('j \d\e F \d\e Y H:i');
@endphp

<body>

    <h1>{{ $export }}</h1>

    <div class="info-header">
        <p><strong>Descripción:</strong> {{ $formulario->descripcion }}</p>
        <p><strong>Generado por:</strong> {{ $user->nombre_completo }}</p>


        <p><strong>Generado el:</strong> {{ $fechaLiteral }}</p>

    </div>

    @if(count($respuestas) > 0)
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($respuestas[0]) as $columna)
                        <th>{{ $columna }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($respuestas as $fila)
                    <tr>
                        @foreach($fila as $valor)
                            <td>{!! $valor !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No hay respuestas registradas para este formulario.</p>
    @endif


    @include('exports.firma', ['user' => $user])
</body>

</html>