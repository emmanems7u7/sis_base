@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Constructor de Formulario: {{ $formulario->nombre }}</h5>

                    </div>
                    <a href="{{ route('formularios.index') }}" class="btn btn-secondary btn-sm"><i
                            class="fas fa-arrow-left me-1"></i>Volver a Formularios</a>


                </div>
            </div>
        </div>
        <div class="col-md-6 mt-3">

            <div class="card shadow-lg">
                <div class="card-body">
                    <h5><i class="fas fa-edit me-2"></i>Construcción de Formularios</h5>

                    <small><i class="fas fa-info-circle me-1"></i>En este módulo puedes diseñar y gestionar formularios
                        personalizados para cualquier propósito dentro del sistema.</small><br>



                    <small><i class="fas fa-arrows-alt me-1"></i>Los campos se pueden <strong>arrastrar</strong> para
                        cambiar su
                        orden de aparición dentro del formulario. El cambio se guarda automáticamente al soltar el
                        campo.</small><br>

                    <small><i class="fas fa-copy me-1"></i>Los formularios pueden ser reutilizados y exportados para
                        distintos
                        módulos según la necesidad.</small><br>
                </div>
            </div>
        </div>

    </div>


    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-1 mb-3">
                <h5 class="mb-0">Crear/Editar Campos</h5>
                <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                    title="Algunos campos, como Checkbox, Radio y Selector, requieren seleccionar una categoría para definir las opciones disponibles."></i>
            </div>
            <!-- Formulario de creación/edición de campo -->
            <form id="formCampo" method="POST" action="{{ route('campos.store', $formulario) }}">
                @csrf

                <div class="row g-3 align-items-end">
                    <!-- Tipo de campo -->
                    <div class="col-md-3">
                        <label for="tipoCampo" class="form-label">Tipo</label>
                        <select id="tipoCampo" name="tipo" class="form-select">
                            <option value="" selected>Seleccione un tipo</option>
                            @foreach ($campos_formulario as $campoForm)
                                <option value="{{ $campoForm->catalogo_codigo }}">{{ $campoForm->catalogo_descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nombre interno -->
                    <div class="col-md-3 position-relative">
                        <label for="nombreCampo" class="form-label d-flex align-items-center gap-1">
                            Nombre interno
                            <i class="fas fa-exclamation-triangle text-primary" data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Recuerda siempre revisar que los nombres internos sean únicos dentro del formulario para evitar conflictos en la captura de datos."></i>
                        </label>
                        <input id="nombreCampo" name="nombre" type="text" class="form-control" placeholder="Nombre interno">
                    </div>

                    <!-- Etiqueta visible -->
                    <div class="col-md-3">
                        <label for="etiquetaCampo" class="form-label">Etiqueta visible</label>
                        <input id="etiquetaCampo" name="etiqueta" type="text" class="form-control"
                            placeholder="Etiqueta visible">
                    </div>

                    <!-- Requerido -->
                    <div class="col-md-1 d-flex align-items-center">
                        <div class="form-check d-flex align-items-center gap-1">
                            <input type="checkbox" id="requeridoCampo" name="requerido" class="form-check-input">
                            <label class="form-check-label mb-0" for="requeridoCampo">Requerido</label>
                            <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Marca la casilla Requerido si deseas que el campo sea obligatorio al completar el formulario."></i>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div id="ContenedorCategoria" style="display:none;">
                        <div class="row">

                            <div class="col-md-3" id="categorias_cont">
                                <label for="categoriaCampo" class="form-label">Categoría</label> <i
                                    class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Seleccione una categoría que se utilizará para completar el tipo seleccionado."></i>
                                <select id="categoriaCampo" name="categoria_id" class="form-select">
                                    <option value="">-- Seleccionar categoría --</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3" id="formularios_cont">
                                <label for="formularioCampo" class="form-label">Formulario</label> <i
                                    class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Seleccione una categoría que se utilizará para completar el tipo seleccionado."></i>
                                <select id="formularioCampo" name="formulario_id" class="form-select">
                                    <option value="">-- Seleccionar formulario --</option>
                                    @foreach($formularios as $form)
                                        <option value="{{ $form->id }}">{{ $form->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <!-- Formulario -->
                            <div class="col-md-6  d-flex align-items-center ">
                                <div class="form-check d-flex align-items-center gap-1">
                                    <input type="checkbox" id="formulario_campo" name="formulario" class="form-check-input">
                                    <label class="form-check-label mb-0" for="formulario_campo">Seleccionar un
                                        formulario</label>
                                    <i class="fas fa-question-circle text-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Marca la casilla si deseas que el campo sean registros de un formulario"></i>
                                </div>
                            </div>
                        </div>


                    </div>


                    <!-- Botones -->
                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button id="btnAgregarCampo" class="btn btn-primary">Agregar campo</button>
                        <button type="button" id="btnCancelarEdicion" class="btn btn-secondary"
                            style="display:none;">Cancelar</button>
                    </div>
                </div>

            </form>
        </div>
    </div>



    <!-- Lista de campos -->
    <div id="listaCampos" class="row g-3 mb-3 mt-3">
        @foreach($campos as $campo)
            <div class="col-md-6" data-id="{{ $campo->id }}">
                <div class="card p-2 shadow-lg position-relative">
                    <!-- Ícono de arrastre en la esquina superior derecha -->
                    <span class="drag-handle position-absolute top-0 end-0 p-2" style="cursor: grab;">
                        <i class="fas fa-arrows-alt"></i>
                    </span>

                    <div class="card-body">
                        @include('formularios.campos.lista_campos', ['campo' => $campo])
                    </div>
                    <div class="card-footer">
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-warning btnEditarCampo" data-id="{{ $campo->id }}">Editar</button>




                            <a type="button" class="btn btn-sm btn-danger " id=""
                                onclick="eliminarCampo('eliminarCampo_{{ $campo->id }}',{{ $campo->id }})">Eliminar</a>

                            <form id="eliminarCampo_{{ $campo->id }}" method="POST"
                                action="{{ route('campos.destroy', $campo) }}" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @include('formularios.campos.modal_busqueda')



@endsection