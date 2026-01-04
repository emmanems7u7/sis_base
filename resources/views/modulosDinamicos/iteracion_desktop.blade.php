

@if($modo === 'mostrar_todos')

    {{-- Mostrar todas las tablas como ya lo tienes --}}
    @foreach($formulariosConRespuestas as $item)
        @include('modulosDinamicos.registros_desktop', ['item' => $item, 'modulo' => $modulo])
    @endforeach

@elseif($modo === 'acordeon')

<div class="accordion" id="accordionFormulariosStayOpen_{{ $modulo->id }}">
    @foreach($formulariosConRespuestas as $index => $item)
        @php
            $formulario = $item['formulario'];
            $respuestas = $item['respuestas'];
        @endphp
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingStayOpen_{{ $formulario->id }}">
                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapseStayOpen_{{ $formulario->id }}" 
                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                        aria-controls="collapseStayOpen_{{ $formulario->id }}">
                        <i class="fas fa-chevron-right me-2"></i>
                        {{ $formulario->nombre }}
                </button>
            </h2>
            <div id="collapseStayOpen_{{ $formulario->id }}" 
                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                 aria-labelledby="headingStayOpen_{{ $formulario->id }}">
                <div class="accordion-body">
                    @include('modulosDinamicos.registros_desktop', ['item' => $item, 'modulo' => $modulo])
                </div>
            </div>
        </div>
    @endforeach
</div>

@elseif($modo === 'pestanas')


<div class="col-lg-12 col-md-12 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
    <div class="nav-wrapper position-relative end-0 custom-border">
        <ul class="nav nav-pills nav-fill p-1" role="tablist">
            @foreach($formulariosConRespuestas as $index => $item)
                @php $formulario = $item['formulario']; @endphp
                <li class="nav-item">
                    <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center {{ $index === 0 ? 'active' : '' }}"
                       href="javascript:;"
                       role="tab"
                       data-target="#formulario_{{ $formulario->id }}">
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
            <div id="formulario_{{ $formulario->id }}" 
                 class="formulario-tab-content {{ $index === 0 ? 'fade show' : 'fade d-none' }}">
                @include('modulosDinamicos.registros_desktop', ['item' => $item, 'modulo' => $modulo])
            </div>
        @endforeach
    </div>
</div>

<script>
const links = document.querySelectorAll('.nav-wrapper .nav-link');

links.forEach(link => {
    link.addEventListener('click', function() {
        // Active
        links.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Mover barra animada (moving-tab)
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

// Inicializar posición de moving-tab al cargar
window.addEventListener('load', () => {
    const activeLink = document.querySelector('.nav-wrapper .nav-link.active');
    if(activeLink){
        const rect = activeLink.getBoundingClientRect();
        const parentRect = activeLink.parentElement.parentElement.getBoundingClientRect();
       
    }
});
</script>

@elseif($modo === 'selector')

{{-- Selector de formulario único --}}
<div class="mb-3">
    <label for="selectorFormularios_{{ $modulo->id }}" class="form-label">Selecciona un formulario:</label>
    <select class="form-select" id="selectorFormularios_{{ $modulo->id }}">
        @foreach($formulariosConRespuestas as $index => $item)
            @php $formulario = $item['formulario']; @endphp
            <option value="{{ $formulario->id }}" {{ $index === 0 ? 'selected' : '' }}>
                {{ $formulario->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div id="formulariosSelector_{{ $modulo->id }}">
    @foreach($formulariosConRespuestas as $index => $item)
        @php $formulario = $item['formulario']; @endphp
        <div class="formulario-tab-content {{ $index === 0 ? 'fade show' : 'fade d-none' }}" id="formulario_{{ $formulario->id }}">
            @include('modulosDinamicos.registros_desktop', ['item' => $item, 'modulo' => $modulo])
        </div>
    @endforeach
</div>

<script>
    const select = document.getElementById('selectorFormularios_{{ $modulo->id }}');

    select.addEventListener('change', function() {
        const selectedId = this.value;
        const forms = document.querySelectorAll('#formulariosSelector_{{ $modulo->id }} .formulario-tab-content');

        forms.forEach(f => {
            if(f.id === 'formulario_' + selectedId) {
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
