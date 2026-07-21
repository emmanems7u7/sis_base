@if ($filtro['tipo'] == 'rango')
    <div class="col-md-12 mb-2">

        <label>
            {{ $nombreCampo }}
        </label>

    </div>


    <div class="col-md-2 mb-3">

        <label>
            Desde
        </label>

        <input type="date" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}][desde]">

    </div>


    <div class="col-md-2 mb-3">

        <label>
            Hasta
        </label>

        <input type="date" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}][hasta]">

    </div>
@else
    <div class="col-md-4 mb-3">

        <label>
            {{ $nombreCampo }}
        </label>


        <input type="date" class="form-control filtro-input" name="filtros[{{ $filtro['campo'] }}]">


    </div>
@endif
