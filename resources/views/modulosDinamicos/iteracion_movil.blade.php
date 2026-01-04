@if($modo === 'mostrar_todos')

    {{-- Mostrar todas las tablas/cards móviles --}}
    <div class="row g-3 mt-2">
        @foreach($formulariosConRespuestas as $item)
            <div class="col-12">
                @include('modulosDinamicos.registros_movil', ['item' => $item, 'modulo' => $modulo])
            </div>
        @endforeach
    </div>

@elseif($modo === 'acordeon')

    {{-- Accordion móvil --}}
    <div class="accordion" id="accordionFormulariosMobile_{{ $modulo->id }}">
        @foreach($formulariosConRespuestas as $index => $item)
            @php
                $formulario = $item['formulario'];
                $respuestas = $item['respuestas'];
            @endphp
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingMobile_{{ $formulario->id }}">
                    <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapseMobile_{{ $formulario->id }}" 
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                            aria-controls="collapseMobile_{{ $formulario->id }}">
                        <i class="fas fa-chevron-right me-2"></i>
                        {{ $formulario->nombre }}
                    </button>
                </h2>
                <div id="collapseMobile_{{ $formulario->id }}" 
                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                     aria-labelledby="headingMobile_{{ $formulario->id }}">
                    <div class="accordion-body">
                        @include('modulosDinamicos.registros_movil', ['item' => $item, 'modulo' => $modulo])
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@elseif($modo === 'pestanas')

    {{-- Pestañas móvil (similar al desktop, pero en full width) --}}
    <div class="col-12 mt-3">
        <div class="nav-wrapper position-relative end-0">
            <ul class="nav nav-pills nav-fill p-1" role="tablist">
                @foreach($formulariosConRespuestas as $index => $item)
                    @php $formulario = $item['formulario']; @endphp
                    <li class="nav-item">
                        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center {{ $index === 0 ? 'active' : '' }}"
                           href="javascript:;"
                           role="tab"
                           data-target="#formularioMobile_{{ $formulario->id }}">
                            <i class="fas fa-file-alt"></i>
                            <span class="ms-2">{{ $formulario->nombre }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
          
        </div>

        <div class="mt-3">
            @foreach($formulariosConRespuestas as $index => $item)
                @php $formulario = $item['formulario']; @endphp
                <div id="formularioMobile_{{ $formulario->id }}" 
                     class="formulario-tab-content {{ $index === 0 ? 'fade show' : 'fade d-none' }}">
                    @include('modulosDinamicos.registros_movil', ['item' => $item, 'modulo' => $modulo])
                </div>
            @endforeach
        </div>
    </div>

    <script>
    const mobileLinks = document.querySelectorAll('.nav-wrapper .nav-link');
   
    mobileLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Active
            mobileLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            // Mover barra animada
            const rect = link.getBoundingClientRect();
            const parentRect = link.parentElement.parentElement.getBoundingClientRect();
           

            // Mostrar formulario con fade
            const targetId = link.dataset.target;
            document.querySelectorAll('.formulario-tab-content').forEach(f => {
                f.classList.remove('show');
                f.classList.add('d-none');
            });
            const target = document.querySelector(targetId);
            target.classList.remove('d-none');
            void target.offsetWidth; // reflow
            target.classList.add('show');
        });
    });

    window.addEventListener('load', () => {
        const activeLink = document.querySelector('.nav-wrapper .nav-link.active');
        if(activeLink){
            const rect = activeLink.getBoundingClientRect();
            const parentRect = activeLink.parentElement.parentElement.getBoundingClientRect();
           
        }
    });
    </script>

@elseif($modo === 'selector')

    {{-- Selector móvil de formulario único --}}
    <div class="mb-3">
        <label for="selectorFormulariosMobile_{{ $modulo->id }}" class="form-label">Selecciona un formulario:</label>
        <select class="form-select" id="selectorFormulariosMobile_{{ $modulo->id }}">
            @foreach($formulariosConRespuestas as $index => $item)
                @php $formulario = $item['formulario']; @endphp
                <option value="{{ $formulario->id }}" {{ $index === 0 ? 'selected' : '' }}>
                    {{ $formulario->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div id="formulariosSelectorMobile_{{ $modulo->id }}">
        @foreach($formulariosConRespuestas as $index => $item)
            @php $formulario = $item['formulario']; @endphp
            <div class="formulario-tab-content {{ $index === 0 ? 'fade show' : 'fade d-none' }}" id="formularioMobile_{{ $formulario->id }}">
                @include('modulosDinamicos.registros_movil', ['item' => $item, 'modulo' => $modulo])
            </div>
        @endforeach
    </div>

    <script>
        const selectMobile = document.getElementById('selectorFormulariosMobile_{{ $modulo->id }}');

        selectMobile.addEventListener('change', function() {
            const selectedId = this.value;
            const forms = document.querySelectorAll('#formulariosSelectorMobile_{{ $modulo->id }} .formulario-tab-content');

            forms.forEach(f => {
                if(f.id === 'formularioMobile_' + selectedId) {
                    f.classList.remove('d-none');
                    void f.offsetWidth; // reflow para activar fade
                    f.classList.add('show');
                } else {
                    f.classList.remove('show');
                    f.classList.add('d-none');
                }
            });
        });
    </script>

@endif
