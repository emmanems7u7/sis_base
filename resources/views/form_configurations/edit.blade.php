<form action="{{ route('formularios.config.update', $formulario->id) }}" method="POST">
    @csrf
    @method('PUT')


    @include('form_configurations._form')

    <button type="submit" class="btn btn-sm btn-primary">

        Guardar Configuración

    </button>
</form>
