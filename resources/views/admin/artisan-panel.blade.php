@extends('layouts.argon')

@section('content')
    <div class="container mt-5">
        <div class="card border-danger shadow">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">Panel Avanzado de Comandos Artisan</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('artisan.run') }}">
                    @csrf
                    <input type="hidden" name="clave_segura" value="{{ $clave_segura }}">
                    <div class="mb-3">
                        <label for="comando" class="form-label">Escribe el comando <code>artisan</code></label>
                        <input type="text" name="comando" class="form-control" placeholder="make:model Cliente" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Ejecutar Comando</button>
                </form>

                @if(isset($output))
                    <div class="alert alert-success mt-3">
                        <pre class="mb-0">{{ $output }}</pre>
                    </div>
                @endif

                @if(isset($error))
                    <div class="alert alert-danger mt-3">
                        {{ $error }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection