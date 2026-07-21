<div class="col-md-4 mb-3">

    <label>
        {{ $filtro['etiqueta'] ?? $filtro['campo'] }}
    </label>


    <select class="form-select filtro-input" name="filtros[{{ $filtro['campo'] }}]">

        <option value="">
            Seleccione
        </option>


        @foreach ($filtro['opciones'] ?? [] as $opcion)
            <option value="{{ $opcion->catalogo_codigo }}">

                {{ $opcion->catalogo_descripcion }}

            </option>
        @endforeach


    </select>

</div>
