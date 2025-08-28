
        
           
               
                    <label>
                        {{ $campo->etiqueta }}
                        @if($campo->requerido)
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    @switch(strtolower($campo->campo_nombre))
                        @case('text')
                            <input type="text" name="{{ $campo->nombre }}" class="form-control" 
                                {{ $campo->requerido ? 'required' : '' }}
                                placeholder="{{ $campo->config['placeholder'] ?? '' }}">
                            @break

                        @case('number')
                            <input type="number" name="{{ $campo->nombre }}" class="form-control" 
                                {{ $campo->requerido ? 'required' : '' }}
                                placeholder="{{ $campo->config['placeholder'] ?? '' }}">
                            @break

                        @case('textarea')
                            <textarea name="{{ $campo->nombre }}" class="form-control" 
                                    {{ $campo->requerido ? 'required' : '' }}
                                    placeholder="{{ $campo->config['placeholder'] ?? '' }}"></textarea>
                            @break
                            @case('checkbox')
                            @foreach($campo->opciones_catalogo as $opcion)
                                <div class="form-check">
                                    <input type="checkbox" 
                                        name="{{ $campo->nombre }}[]" 
                                        value="{{ $opcion->catalogo_codigo }}" 
                                        class="form-check-input"
                                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                    <label class="form-check-label" for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                        {{ $opcion->catalogo_descripcion }}
                                    </label>
                                </div>
                            @endforeach
                            @break

                            @case('radio')
                            @foreach($campo->opciones_catalogo as $opcion)
                                <div class="form-check">
                                    <input type="radio" 
                                        name="{{ $campo->nombre }}" 
                                        value="{{ $opcion->catalogo_codigo }}" 
                                        class="form-check-input"
                                        id="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                    <label class="form-check-label" for="{{ $campo->nombre }}_{{ $opcion->catalogo_codigo }}">
                                        {{ $opcion->catalogo_descripcion }}
                                    </label>
                                </div>
                            @endforeach
                            @break

                            @case('selector')
                            <select name="{{ $campo->nombre }}" class="form-select">
                              
                                <option value="">Seleccione una opción</option>
                                    
                                @foreach($campo->opciones_catalogo as $opcion)

                                   <option value="{{ $opcion->catalogo_codigo }}">
                                    {{ $opcion->catalogo_descripcion }}
                                </option>
                                @endforeach
                            </select>
                            @break

                        @default
                            <input type="text" name="{{ $campo->nombre }}" class="form-control">
                    @endswitch
              
           

    