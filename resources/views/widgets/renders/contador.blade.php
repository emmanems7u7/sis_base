<div class="card">
    <div class="card-body p-3">

        <div class="row">

            <div class="col-8">

                <div class="numbers">

                    <p class="text-sm mb-0 text-uppercase font-weight-bold" style="color: {{ $widget['data']['color'] }}">
                        {{ $widget['data']['titulo'] }}
                    </p>

                    <h5 class="font-weight-bolder">
                        {{ $widget['data']['prefijo'] }}
                        {{ number_format($widget['data']['contador']) }}
                        {{ $widget['data']['sufijo'] }}
                    </h5>

                    @if ($widget['data']['mostrar_descripcion'] && !empty($widget['data']['descripcion']))
                        <p class="mb-0">
                            {{ $widget['data']['descripcion'] }}
                        </p>
                    @endif

                </div>

            </div>

            <div class="col-4 text-end">

                @if ($widget['data']['mostrar_icono'])
                    <div class="icon icon-shape shadow text-center rounded-circle"
                        style="
                            background: {{ $widget['data']['color'] }};
                            color: white;
                            width: 50px;
                            height: 50px;
                            margin-left: auto;
                        ">
                        <i class="{{ $widget['data']['icono'] }} text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                @endif

            </div>

        </div>

    </div>
</div>
