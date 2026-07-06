@php

    /*
    |--------------------------------------------------------------------------
    | TIPOS DISPONIBLES
    |--------------------------------------------------------------------------
    */

    $types = [
        'buttons' => 'Botón',
        'messages' => 'Mensaje',
        'validations' => 'Validación',
        'titles' => 'Título',
        'datatables' => 'Datatable',
        'alerts' => 'Alerta',
    ];

@endphp




{{-- PREDEFINIDOS --}}
@foreach ($fields as $section => $items)
    <div class="mb-5">

        <h6 class="border-bottom pb-2 mb-4">

            {{ $section }}

        </h6>

        <div class="row">

            @foreach ($items as $item)
                @php

                    $value = data_get($config?->config ?? [], $item['key'] . '.text');

                    $icon = data_get($config?->config ?? [], $item['key'] . '.icon');

                @endphp

                <div class="col-6 col-md-4 col-lg-2 mb-4">

                    <label class="form-label fw-bold">

                        {{ $item['label'] }}

                    </label>

                    {{-- TEXTO --}}
                    <input type="text" class="form-control mb-2" name="defaults[{{ $item['key'] }}][text]"
                        value="{{ $value }}" placeholder="Texto">

                    {{-- ICONOS SOLO BUTTON --}}
                    @if (str_contains($item['key'], 'buttons.') || str_contains($item['key'], 'titles.'))
                        <div class="input-group">

                            <span class="input-group-text icon-preview">

                                @if ($icon)
                                    <i class="{{ $icon }}"></i>
                                @else
                                    <i class="fas fa-icons"></i>
                                @endif

                            </span>

                            <input type="text" class="form-control icon-input"
                                name="defaults[{{ $item['key'] }}][icon]" value="{{ $icon }}"
                                placeholder="fas fa-save">

                        </div>
                    @endif

                </div>
            @endforeach

        </div>

    </div>
@endforeach





<script>
    let customIndex = {{ count($customs ?? []) }};

    /*
    |--------------------------------------------------------------------------
    | AGREGAR CUSTOM
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('add-custom')
        .addEventListener('click', function() {

            let html = document
                .getElementById('custom-template')
                .innerHTML;

            html = html
                .replace('__TYPE__', `custom[${customIndex}][type]`)
                .replace('__KEY__', `custom[${customIndex}][key]`)
                .replace('__VALUE__', `custom[${customIndex}][value]`)
                .replace('__ICON__', `custom[${customIndex}][icon]`);

            document
                .getElementById('custom-container')
                .insertAdjacentHTML('beforeend', html);

            customIndex++;

        });

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function(e) {

        if (e.target.classList.contains('remove-custom')) {

            e.target.closest('.custom-item').remove();

        }

    });

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR ICONO SI ES BUTTON
    |--------------------------------------------------------------------------
    */

    document.addEventListener('change', function(e) {

        if (e.target.classList.contains('custom-type')) {

            const card = e.target.closest('.custom-item');

            const iconWrapper = card.querySelector('.icon-wrapper');

            if (e.target.value === 'buttons' || e.target.value === 'titles') {

                iconWrapper.style.display = '';

            } else {

                iconWrapper.style.display = 'none';

            }

        }

    });

    /*
    |--------------------------------------------------------------------------
    | PREVISUALIZAR ICONOS
    |--------------------------------------------------------------------------
    */

    document.addEventListener('input', function(e) {

        if (e.target.classList.contains('icon-input')) {

            const preview = e.target
                .closest('.input-group')
                .querySelector('.icon-preview');

            const iconClass = e.target.value.trim();

            if (iconClass !== '') {

                preview.innerHTML = `<i class="${iconClass}"></i>`;

            } else {

                preview.innerHTML = `<i class="fas fa-icons"></i>`;

            }

        }

    });
</script>
