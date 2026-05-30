
<div id="formulario-dinamico">
<div class="row g-4">
@foreach($campos as $campo)
@php
    $tipo = strtolower($campo->campo_nombre);

    $tiposSinLayout = ['campo autocompletado','hidden','asociado'];
    $esHidden = in_array($tipo, $tiposSinLayout);

    $cols = $cols ?? 2;

    if (!empty($campo->config['columna'])) {
        $colClass = $campo->config['columna'];
    } 
 
    elseif ($tipo === 'textarea') {
        $colClass = 'col-12';
    } 
    else {
        $grid = $formulario->grid;

       $colClass =$grid;
    }

    $esRequerido = false;
    if(isset($formulario->config['registro_multiple']) && !$formulario->config['registro_multiple']) {
        $esRequerido = isset($requerido)
            ? (bool) $requerido
            : (bool) $campo->requerido;
    }

    $mostrarAsterisco = isset($requerido)
        ? (bool) $requerido
        : (bool) $campo->requerido;

    $valoresCampo = $valores[$campo->id] ?? [];
    if (isset($formulario->config['registro_multiple']) && $formulario->config['registro_multiple']) 
    {
    $valor = '';
    } else {
        $valor = old($campo->id, $valoresCampo[0] ?? '');
    }
    $prefix = $prefix ?? '';
    $inputName = $prefix ? "{$prefix}[{$campo->id}]" : $campo->id;
    $etiqueta =$campo->etiqueta;
    $inputId = $prefix 
        ? "{$prefix}_{$campo->id}" 
        : $campo->id;
@endphp

@if(!$esHidden)
<div class="{{ $colClass }}">
@endif
@if(!$esHidden)
    <div class="d-flex align-items-center gap-1 mb-1">
        <label class="form-label fw-bold mb-0">
            {{ $campo->etiqueta }}
            @if($mostrarAsterisco)
                <span class="text-danger">*</span>
            @endif
        </label>

        @if($tipo == 'selector')
          
        <i class="fas fa-search text-secondary btn-buscar-opcion"
   style="cursor: pointer;"
   data-bs-toggle="modal"
   data-bs-target="#modalBusqueda"
   data-campo-id="{{ $campo->id }}"
   title="Buscar"></i>
            
        @endif
    </div>
@endif
    @switch($tipo)

        {{-- INPUTS BÁSICOS --}}
        @case('text')
        @case('email')
        @case('number')
        @case('password')
        @case('enlace')
          @include('formularios.componentes.input_basico')
        @break

        {{-- TEXTAREA --}}
        @case('textarea')
          @include('formularios.componentes.textarea')
            
        @break

        {{-- FECHA --}}
        @case('fecha')
          @include('formularios.componentes.fecha')
           
        @break

        {{-- HORA --}}

        @case('hora')
          @include('formularios.componentes.hora')
           
        @break

        {{-- SELECTOR --}}
        @case('selector')
          @include('formularios.componentes.selector')
           
        @break

        {{-- CHECKBOX --}}
        @case('checkbox')
          @include('formularios.componentes.checkbox')
            
        @break

        {{-- RADIO --}}
        @case('radio')
          @include('formularios.componentes.radio')
            
        @break

        {{-- ARCHIVOS --}}
        @case('archivo')
        @include('formularios.componentes.archivo')
        
        @break
        {{-- IMAGEN --}}

        @case('imagen')
        @include('formularios.componentes.imagen')
           
        @break
        {{-- VIDEO --}}

        @case('video')
        @include('formularios.componentes.video')
           
        @break

        {{-- COLOR --}}
        @case('color')
        @include('formularios.componentes.color')

        @break

        {{-- AUTOCOMPLETADO (HIDDEN) --}}
        @case('campo autocompletado')
        @include('formularios.componentes.autocompletado')
        @break

        {{-- CAMPO RELACION --}}
        @case('campo_relacion')
        @include('formularios.componentes.relacion')

        @break


         {{-- CAMPO IDENTIFICADOR --}}
         @case('identificador')
        @include('formularios.componentes.identificador')

        @break

        
         {{-- CAMPO HIDDEN --}}
         @case('hidden')
        @include('formularios.componentes.hidden')

        @break

        @case('asociado')
        @include('formularios.componentes.hidden')

        @break

    @endswitch

@if(!$esHidden)
</div>
@endif

@endforeach


</div>
</div>




@include('formularios.campos.modal_busqueda')



