@if ($filtro['tipo'] == 'rango')
    <div class="col-md-6 mb-3">

        <label>
            {{ $nombreCampo }} Desde
        </label>


        <input type="number" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}][desde]">

    </div>


    <div class="col-md-6 mb-3">

        <label>
            {{ $nombreCampo }} Hasta
        </label>


        <input type="number" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}][hasta]">

    </div>
@else
    <div class="col-md-4 mb-3">

        <label>
            {{ $nombreCampo }}
        </label>


        <input type="number" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}]">

    </div>
@endif
