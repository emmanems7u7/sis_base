<div class="card mt-3 shadow-lg">
    <div class="card-body">

        <h6>Formulario: {{ $formulario->nombre }}</h6>
        <form
            action="{{ route('formularios.responder', ['form' => $formulario->id, 'modulo' => $modulo, 'tipo' => 1]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @include('formularios._campos', ['campos' => $formulario->campos->sortBy('posicion'), 'valores' => []])


            <button type="submit" class="btn btn-primary mt-3">Registrar</button>
        </form>
    </div>
</div>