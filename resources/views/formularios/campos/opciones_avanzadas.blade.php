<div class="tab-pane fade" id="content-avanzadas" role="tabpanel" aria-labelledby="tab-avanzadas">

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h6 class="fw-bold mb-2">
                Agrupación automática de registros
            </h6>

            <p class="text-muted small">
                Permite evitar registros duplicados cuando todos los campos coinciden.
                Si se activa, podrás seleccionar un campo numérico que se incrementará automáticamente
                en lugar de generar múltiples registros.
            </p>

            <!-- CHECK AGRUPACIÓN -->
            <div class="form-check form-switch mb-3">
                <input class="form-check-input"
                       type="checkbox"
                       id="permitirAgrupacion"
                       {{ $formulario->config['agrupacion']['activa'] ?? false ? 'checked' : '' }}>
                <label class="form-check-label" for="permitirAgrupacion">
                    Permitir agrupación de registros
                </label>
            </div>

            <!-- SELECT CAMPO INCREMENTO -->
            <div id="contenedorCampoIncremento"
                 style="display: {{ $formulario->config['agrupacion']['activa'] ?? false ? 'block' : 'none' }};">

                <label class="form-label fw-semibold">
                    Campo que se incrementará automáticamente
                </label>

                <select id="campoIncremento" class="form-select form-select-sm">
                    <option value="">Seleccione un campo numérico</option>

                    @foreach($campos as $campo)
                        
                            <option value="{{ $campo->id }}"
                                {{ ($formulario->config['agrupacion']['campo_incremento'] ?? null) == $campo->id ? 'selected' : '' }}>
                                {{ $campo->etiqueta }}
                            </option>
                     
                    @endforeach

                </select>

              
            </div>

            <!-- BOTÓN GUARDAR -->
            <div class="mt-4">
                <button type="button"
                        class="btn btn-sm btn-primary"
                        id="guardarConfiguracionAgrupacion">
                    Guardar configuración
                </button>
            </div>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const checkAgrupacion = document.getElementById('permitirAgrupacion');
    const contenedorCampo = document.getElementById('contenedorCampoIncremento');

    checkAgrupacion.addEventListener('change', function(){
        contenedorCampo.style.display = this.checked ? 'block' : 'none';
    });




    const btnGuardar = document.getElementById('guardarConfiguracionAgrupacion');

    if(btnGuardar){

        btnGuardar.addEventListener('click', async function(){

            const activa = document.getElementById('permitirAgrupacion').checked;
            const campoIncremento = document.getElementById('campoIncremento').value;

            if(activa && !campoIncremento){
                alertify.error('Debe seleccionar el campo que se incrementará.');
                return;
            }

            try{

                const response = await fetch("{{ route('formularios.guardarAgrupacion', $formulario->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        activa: activa,
                        campo_incremento: campoIncremento
                    })
                });

                const data = await response.json();

                if(data.success){
                    alertify.success(data.message);
                }else{
                    alertify.error('No se pudo guardar la configuración.');
                }

            }catch(error){
                alertify.error('Error en el servidor.');
            }

        });

    }



});
</script>