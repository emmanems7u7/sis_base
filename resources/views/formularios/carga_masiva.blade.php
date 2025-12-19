@extends('layouts.argon')

@section('content')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Carga Masiva de información</h5>

                    <a href="{{ route('formularios.descargar.plantilla', $form) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Descargar plantilla
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">


                    <i class="fas fa-file-upload me-2 fa-sm text-info"></i>
                    <div class="small">
                        <strong>Carga masiva:</strong> selecciona un archivo <code>.txt</code> o <code>.csv</code> con los
                        datos separados por comas. <br>
                        <i class="fas fa-info-circle text-secondary"></i> Los campos de tipo <em>imagen, video o
                            archivo</em> deben contener solo una <strong>ruta genérica</strong>. <br>
                        <i class="fas fa-exclamation-triangle text-warning"></i> Los <strong>errores detectados</strong> se
                        mostrarán en el contenedor inferior después de la importación.
                    </div>



                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="archivoImport" class="form-label fw-bold">
                            <i class="fas fa-file-upload me-2"></i>Selecciona archivo (.txt/.csv)
                        </label>
                        <input type="file" id="archivoImport" class="form-control form-control-lg" accept=".txt,.csv">
                    </div>

                    <button id="btnImport" class="btn btn-primary btn-lg mb-3">
                        <i class="fas fa-upload me-2"></i>Importar
                    </button>

                    <div class="progress rounded-pill" style="height: 25px;">
                        <div class="progress-bar bg-gradient-success fw-bold text-dark" role="progressbar"
                            style="width: 0%;" id="barraProgreso">0%</div>
                    </div>


                </div>
            </div>

            <style>
                #erroresImport {
                    max-height: 250px;
                    overflow-y: auto;
                    border: 1px solid #f0f0f0;
                    border-radius: .375rem;
                    padding: 10px;
                    background-color: #fff3f3;
                    color: #c82333;
                    font-size: 0.9rem;
                }
            </style>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg mt-3">
                <div class="card-body">
                    <h5 class="mb-3 text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Errores encontrados:
                    </h5>

                    <div id="erroresImport" class="mt-3">

                    </div>
                </div>
            </div>

        </div>
    </div>



    <script>
        document.getElementById('btnImport').addEventListener('click', async () => {
            const archivo = document.getElementById('archivoImport').files[0];
            if (!archivo) return alertify.error('Selecciona un archivo primero.');

            const formData = new FormData();
            formData.append('archivo', archivo);

            // 1️⃣ Subir archivo
            const resSubir = await fetch("{{ route('import.subir') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const dataSubir = await resSubir.json();
            if (!dataSubir.success) return alert(dataSubir.message);

            // 2️⃣ Procesar por chunks
            const erroresTotales = [];
            let finalizado = false;
            while (!finalizado) {
                const resChunk = await fetch("{{ route('import.procesar') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ form_id: {{ $form }} })
                });
                const dataChunk = await resChunk.json();

                if (dataChunk.errores.length > 0) {
                    erroresTotales.push(...dataChunk.errores);
                }

                document.getElementById('barraProgreso').style.width = dataChunk.progreso + '%';
                document.getElementById('barraProgreso').innerText = dataChunk.progreso + '%';

                finalizado = dataChunk.finalizado;
            }

            if (erroresTotales.length > 0) {
                document.getElementById('erroresImport').innerHTML = erroresTotales.join('<br>');
            } else {
                alertify.success('Importación completada sin errores.');
            }
        });
    </script>
@endsection