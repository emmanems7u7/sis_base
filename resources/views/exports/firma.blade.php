@php

    use App\Models\Configuracion;


@endphp

@if(Configuracion::first()->firma)
    <footer style="font-size: 10px; text-align: center; position: absolute; bottom: 90; left: 0; right: 0;">
        ___________________________<br>
        <strong>{{ $user->nombre_completo }}</strong><br>
        Firma del profesional
    </footer>
@endif