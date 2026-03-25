<label class="form-label small mb-1">
    {{ $campo->etiqueta }}
    @if($campo->requerido)
        <span class="text-danger">*</span>
    @endif
   
</label>
@if($campo->campo_nombre == 'campo autocompletado')
<i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Este campo es de tipo Hidden, por lo tanto no será visible en el formulario para el usuario. Sin embargo, se completará automáticamente con el valor que usted seleccione para su autocompletado."></i>
   
   @endif

   @if($campo->campo_nombre == 'campo_relacion')
<i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="Este campo es de tipo referencial, se registrara el contenido del campo seleccionado correspondiente al formulario con el que se tiene la relación"></i>
   
   @endif

 
@switch(strtolower($campo->campo_nombre))

    @case('text')
        <input type="text" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('number')
        <input type="number" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('textarea')
        <textarea name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1" 
            rows="2"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}"></textarea>
        @break

    @case('checkbox')
        <div class="opciones-container mb-1" data-campo-id="{{ $campo->id }}">
            @foreach($campo->opciones_catalogo as $opcion)
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="{{ $campo->nombre }}[]" 
                        value="{{ $opcion->catalogo_codigo }}" 
                        class="form-check-input form-check-input-sm"
                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    <label class="form-check-label small" 
                        for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </label>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-xs btn-primary mt-1 btn-ver-mas-checkbox" 
            data-campo-id="{{ $campo->id }}">
            Ver más
        </button>
        @break

    @case('radio')
        <div class="radio-container mb-1" data-campo-id="{{ $campo->id }}">
            @foreach($campo->opciones_catalogo as $opcion)
                <div class="form-check form-check-inline">
                    <input type="radio" name="{{ $campo->nombre }}" 
                        value="{{ $opcion->catalogo_codigo }}" 
                        class="form-check-input form-check-input-sm"
                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                    <label class="form-check-label small" 
                        for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </label>
                </div>
            @endforeach
            <button type="button" class="btn btn-xs btn-primary mt-1 btn-ver-mas">
                Ver más
            </button>
        </div>
        @break

    @case('selector')
        <div class="d-flex align-items-center gap-1 mb-1">
            <select name="{{ $campo->nombre }}" 
                class="form-select form-select-sm tom-select campo-dinamico" 
                data-campo-id="{{ $campo->id }}">
                <option value="">Seleccione...</option>
                @foreach($campo->opciones_catalogo as $opcion)
                    <option value="{{ $opcion->catalogo_codigo }}">
                        {{ $opcion->catalogo_descripcion }}
                    </option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary btn-xs btn-buscar-opcion" 
                data-bs-toggle="modal" 
                data-bs-target="#modalBuscarOpcion"
                data-campo-id="{{ $campo->id }}">
                <i class="fas fa-search"></i>
            </button>
        </div>
        @break

    @case('imagen')
        <input type="file" name="{{ $campo->nombre }}" accept="image/*" 
            class="form-control form-control-sm mb-1" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('video')
        <input type="file" name="{{ $campo->nombre }}" accept="video/*" 
            class="form-control form-control-sm mb-1" 
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('enlace')
        <input type="url" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="https://...">
        @break

    @case('fecha')
        <input type="date" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('hora')
        <input type="time" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('archivo')
        <input type="file" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('color')
        <input type="color" name="{{ $campo->nombre }}" 
            class="form-control form-control-color form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}>
        @break

    @case('email')
        <input type="email" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

    @case('password')
        <input type="password" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1"
            {{ $campo->requerido ? 'required' : '' }}
            placeholder="{{ $campo->config['placeholder'] ?? '' }}">
        @break

   

    @case('campo autocompletado')
      
            <input type="text" name="{{ $campo->nombre }}" 
                class="form-control form-control-sm mb-1 campo-autocompletado" 
                data-campo-id="{{ $campo->id }}"
                placeholder="{{ $campo->config['placeholder'] ?? '' }}"
                value="{{ $campo->config['autocompletar'] ?? ''; }}">  
            <button class="btn btn-xs btn-dark btn_autocompletado" >Guardar</button>´


            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    document.querySelectorAll('.btn_autocompletado').forEach(button => {

                        button.addEventListener('click', function (e) {
                            e.preventDefault();

                            const input = this.previousElementSibling;

                            const campoId = input.dataset.campoId;
                            const nombreCampo = input.name;
                            const valor = input.value;

                            fetch('/guardar/campo/autocompletado', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    
                                    campo_id: campoId,
                                    nombre: nombreCampo,
                                    valor: valor
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(data.success)
                                {
                                    input.value = data.valor;
                                    mostrarAlerta('error', data.message);
                                }
                            
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });

                        });

                    });

                });
                </script>

                            @break


                            @case('campo_relacion')

                <div class="config-campo">

                    @include('formularios.campos.carga_campos')

                    <button class="btn btn-xs btn-dark boton_relacion"
                            data-campo="{{ $campo->id }}">
                        Guardar
                    </button>

                </div>

         
                @break


            


    @default
        <input type="text" name="{{ $campo->nombre }}" 
            class="form-control form-control-sm mb-1">
@endswitch