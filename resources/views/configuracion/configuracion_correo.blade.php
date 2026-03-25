@extends('layouts.argon')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="container">
                <h1>Configuración de Correo</h1>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('configuracion_correo.update') }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="host" class="form-label">Host</label>
                        <input type="text" name="host" id="host" class="form-control @error('host') is-invalid @enderror"
                            value="{{ old('host', $config->host ?? '') }}" required>
                        @error('host')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="port" class="form-label">Puerto</label>
                        <input type="number" name="port" id="port" class="form-control @error('port') is-invalid @enderror"
                            value="{{ old('port', $config->port ?? '') }}" required>
                        @error('port')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario (Email)</label>
                        <input type="email" name="username" id="username"
                            class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username', $config->username ?? '') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            value="{{ old('password', $config->password ?? '') }}" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="encryption" class="form-label">Encriptación (ssl, tls o vacío)</label>
                        <input type="text" name="encryption" id="encryption"
                            class="form-control @error('encryption') is-invalid @enderror"
                            value="{{ old('encryption', $config->encryption ?? '') }}">
                        @error('encryption')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="from_address" class="form-label">Correo remitente</label>
                        <input type="email" name="from_address" id="from_address"
                            class="form-control @error('from_address') is-invalid @enderror"
                            value="{{ old('from_address', $config->from_address ?? '') }}" required>
                        @error('from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="from_name" class="form-label">Nombre remitente</label>
                        <input type="text" name="from_name" id="from_name"
                            class="form-control @error('from_name') is-invalid @enderror"
                            value="{{ old('from_name', $config->from_name ?? '') }}" required>
                        @error('from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar configuración</button>
                </form>

                @if($config)
                    <div class="card mt-3">
                        <div class="card-body">
                            <p>Prueba de envio de correo</p>

                            <p onclick="enviarPrueba()" class="" style="cursor: pointer;">
                                <strong class="text-primary fw-bold"> Enviar correo de prueba a
                                </strong>{{ $config->from_address }}
                            </p>

                        </div>

                    </div>
                @else
                @endif
            </div>
        </div>
    </div>

    <script>
        function enviarPrueba() {
            const url = "{{ route('correo.prueba') }}";
            const loader = document.getElementById('loader');
            loader.style.display = '';

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        loader.style.display = 'none';
                        throw new Error('Error al enviar la prueba');
                    }
                    return response.json();
                })
                .then(data => {
                    loader.style.display = 'none';
                    mostrarAlerta('success', 'Correo de prueba enviado con éxito');
                })
                .catch(error => {
                    loader.style.display = 'none';
                    mostrarAlerta('error', 'Error al enviar correo de prueba');

                });
        }

    </script>
@endsection