<div id="programacion-configuracion">

    <div class="d-flex align-items-center mb-3">

        <i class="fas fa-clock text-primary me-2"></i>

        <h5 class="mb-0">
            Configuración de Programación
        </h5>

    </div>

    <hr class="mt-2 mb-4">

    <div class="row g-3">

        {{-- Tipo de programación --}}
        <div class="col-md-6">

            <label class="form-label">
                Tipo de programación
            </label>

            <select class="form-select" id="programacion_tipo" name="programacion[tipo]">

                <option value="once">
                    Ejecutar una sola vez
                </option>

                <option value="daily">
                    Diariamente
                </option>

                <option value="weekly">
                    Semanalmente
                </option>

                <option value="monthly">
                    Mensualmente
                </option>

                <option value="interval">
                    Cada cierto tiempo
                </option>

            </select>

        </div>

        {{-- Hora --}}
        <div class="col-md-6">

            <label class="form-label">
                Hora
            </label>

            <input type="time" class="form-control" id="programacion_hora" name="programacion[hora]">

        </div>

        {{-- Fecha única --}}
        <div class="col-md-6" id="bloque-fecha-unica">

            <label class="form-label">
                Fecha
            </label>

            <input type="date" class="form-control" name="programacion[fecha]">

        </div>

        {{-- Semanal --}}
        <div class="col-12 d-none" id="bloque-semanal">

            <label class="form-label">
                Días de ejecución
            </label>

            <div class="row">

                @php
                    $dias = [
                        1 => 'Lunes',
                        2 => 'Martes',
                        3 => 'Miércoles',
                        4 => 'Jueves',
                        5 => 'Viernes',
                        6 => 'Sábado',
                        0 => 'Domingo',
                    ];
                @endphp

                @foreach ($dias as $k => $v)
                    <div class="col-md-3 col-6 mb-2">

                        <div class="form-check">

                            <input class="form-check-input" type="checkbox" name="programacion[dias][]"
                                value="{{ $k }}" id="dia{{ $k }}">

                            <label class="form-check-label" for="dia{{ $k }}">

                                {{ $v }}

                            </label>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

        {{-- Mensual --}}
        <div class="col-md-4 d-none" id="bloque-mensual">

            <label class="form-label">
                Día del mes
            </label>

            <input type="number" min="1" max="31" class="form-control" name="programacion[dia_mes]">

        </div>

        {{-- Intervalo --}}
        <div class="col-md-4 d-none" id="bloque-intervalo">

            <label class="form-label">
                Cada
            </label>

            <input type="number" min="1" value="5" class="form-control" name="programacion[cada]">

        </div>

        <div class="col-md-4 d-none" id="bloque-intervalo2">

            <label class="form-label">
                Unidad
            </label>

            <select class="form-select" name="programacion[unidad]">

                <option value="minutes">
                    Minutos
                </option>

                <option value="hours">
                    Horas
                </option>

            </select>

        </div>

        {{-- Fecha inicio --}}
        <div class="col-md-6">

            <label class="form-label">
                Fecha inicio
            </label>

            <input type="date" class="form-control" name="programacion[inicio]">

        </div>

        {{-- Fecha fin --}}
        <div class="col-md-6">

            <label class="form-label">
                Fecha fin
            </label>

            <input type="date" class="form-control" name="programacion[fin]">

        </div>

    </div>

</div>
