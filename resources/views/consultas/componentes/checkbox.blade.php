<div class="col-md-4 mb-3">

    <label>
        {{ $filtro['etiqueta'] ?? $filtro['campo'] }}
    </label>


    <div class="border rounded p-2">

        @foreach ($filtro['opciones'] ?? [] as $opcion)
            <div class="form-check">

                <input class="form-check-input filtro-input" type="checkbox" name="filtros[{{ $filtro['campo'] }}][]"
                    value="{{ $opcion->catalogo_codigo }}" id="check_{{ $filtro['campo'] }}_{{ $loop->index }}">


                <label class="form-check-label" for="check_{{ $filtro['campo'] }}_{{ $loop->index }}">
                    {{ $opcion->catalogo_descripcion }}
                </label>

            </div>
        @endforeach

    </div>

</div>
