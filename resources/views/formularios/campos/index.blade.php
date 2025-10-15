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

                    <button class="btn btn-primary ms-2 btn-sm ver-formulario" data-id="{{ $formulario->id }}">
                        <i class="fas fa-eye"></i> Ver Formulario
                    </button>
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


    @include('formularios.campos.modal_visor')


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chkFormulario = document.getElementById('formulario_campo');
            const contCategorias = document.getElementById('categorias_cont');
            const contFormularios = document.getElementById('formularios_cont');



            // evento al cambiar el checkbox
            chkFormulario.addEventListener('change', actualizarVisibilidad);

            // establecer estado inicial
            actualizarVisibilidad();
        });

        // función para actualizar visibilidad
        function actualizarVisibilidad() {
            const chkFormulario = document.getElementById('formulario_campo');
            const contCategorias = document.getElementById('categorias_cont');
            const contFormularios = document.getElementById('formularios_cont');

            if (chkFormulario.checked) {
                contFormularios.style.display = 'block';
                contCategorias.style.display = 'none';
            } else {
                contFormularios.style.display = 'none';
                contCategorias.style.display = 'block';
            }
        }
    </script>



    <script>
        // Drag & Drop


        const lista = document.getElementById('listaCampos');

        new Sortable(lista, {
            animation: 150,
            handle: '.drag-handle', // solo arrastrable desde el ícono
            draggable: '.col-md-6', // cada columna arrastrable
            onEnd: function () {
                let orden = [...lista.querySelectorAll('.col-md-6')].map(item => item.dataset.id);

                fetch("{{ route('formularios.campos.reordenar', $formulario) }}", {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ orden })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            alertify.success('Orden guardado correctamente');
                        } else {
                            alertify.error('Error al guardar el orden');
                        }
                    });
            }
        });

        let editId = null;

        // Mostrar selector de categoría según tipo
        const tipoCampo = document.getElementById('tipoCampo');
        const categoriaCampo = document.getElementById('ContenedorCategoria');

        tipoCampo.addEventListener('change', function () {

            const tipoTexto = this.options[this.selectedIndex].textContent.trim();
            if (['Radio', 'Checkbox', 'Selector', 'Imagen', 'Video', 'Archivo'].includes(tipoTexto)) {

                categoriaCampo.style.display = '';
                document.getElementById('categoriaCampo').style.display = 'inline-block';
            } else {


                categoriaCampo.style.display = 'none';
                categoriaCampo.value = '';
            }
        });



        // Cancelar edición
        document.getElementById('btnCancelarEdicion').addEventListener('click', function () {
            alertify.error("Edición cancelada");
            editId = null;
            document.getElementById('tipoCampo').value = '';
            document.getElementById('nombreCampo').value = '';
            document.getElementById('etiquetaCampo').value = '';
            document.getElementById('requeridoCampo').checked = false;
            categoriaCampo.style.display = 'none';
            categoriaCampo.value = '';
            this.style.display = 'none';
            document.getElementById('btnAgregarCampo').textContent = 'Agregar campo';
            const form = document.getElementById('formCampo');
            form.action = "{{ route('campos.store', $formulario) }}";
            form.querySelector('input[name="_method"]')?.remove();

        });

        // Editar campo


        // Función para cargar los datos del campo en el formulario
        function cargarCampo(editId) {
            fetch(`/campos/${editId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alertify.error('No se pudo cargar el campo');
                        return;
                    }

                    const campo = data.campo;
                    // Llenar form
                    document.getElementById('tipoCampo').value = campo.tipo;
                    document.getElementById('nombreCampo').value = campo.nombre;
                    document.getElementById('etiquetaCampo').value = campo.etiqueta;
                    document.getElementById('requeridoCampo').checked = campo.requerido;
                    var catCampo = document.getElementById('ContenedorCategoria');

                    if (campo.categoria_id) {

                        catCampo.style.display = 'inline-block';
                        catCampo.value = campo.categoria_id;

                    } else if (campo.form_ref_id) {
                        document.getElementById('formulario_campo').checked = true;
                        catCampo.style.display = 'inline-block';
                        actualizarVisibilidad();
                    }
                    else {
                        catCampo.style.display = 'none';
                        catCampo.value = '';
                    }

                    // Cambiar acción del form para edición
                    const form = document.getElementById('formCampo');
                    form.action = `/campos/${editId}`;
                    form.method = 'POST';

                    // Agregar hidden para PUT
                    let inputMethod = form.querySelector('input[name="_method"]');
                    if (!inputMethod) {
                        inputMethod = document.createElement('input');
                        inputMethod.type = 'hidden';
                        inputMethod.name = '_method';
                        form.appendChild(inputMethod);
                    }
                    inputMethod.value = 'PUT';

                    // Cambiar texto de botón y mostrar cancelar
                    document.getElementById('btnAgregarCampo').textContent = 'Actualizar campo';
                    document.getElementById('btnCancelarEdicion').style.display = 'inline-block';
                    alertify.success("Ahora puede editar el campo seleccionado");

                })
                .catch(() => alertify.error('Error al cargar el campo'));
        }

        // Editar campo
        document.querySelectorAll('.btnEditarCampo').forEach(btn => {
            btn.addEventListener('click', function () {
                const editId = this.getAttribute('data-id');

                // Verificar si el formulario tiene respuestas antes de permitir editar
                fetch(`/campos/${editId}/check-respuestas`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(check => {
                        if (check.tiene_respuestas) {
                            alertify.confirm(
                                'Advertencia',
                                'Este campo pertenece a un formulario que ya tiene respuestas. ¿Seguro que quieres editarlo?',
                                function () {
                                    // Confirmó: cargar campo
                                    cargarCampo(editId);
                                },
                                function () {
                                    // Canceló
                                    alertify.error('Edición cancelada');
                                }
                            );
                        } else {
                            // No hay respuestas, cargar campo directamente
                            cargarCampo(editId);
                        }
                    })
                    .catch(() => alertify.error('Error al verificar respuestas del formulario'));
            });
        });


        //eliminar campo
        function eliminarCampo(id, campo) {
            // Verificar si el formulario tiene respuestas antes de permitir editar
            fetch(`/campos/${campo}/check-respuestas`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(check => {
                    if (check.tiene_respuestas) {
                        alertify.confirm(
                            'Advertencia',
                            'Este campo pertenece a un formulario que ya tiene respuestas. ¿Seguro que quieres eliminarlo?',
                            function () {
                                document.getElementById(id).submit();

                                confirmarEliminacion(id, '¿Estás seguro de que deseas eliminar este Campo?')

                            },
                            function () {
                                // Canceló
                                alertify.error('Eliminación cancelada');
                            }
                        );
                    } else {
                        alertify.confirm(
                            'Confirmar eliminación',
                            '¿Estás seguro de que deseas eliminar este Campo?',
                            function () {
                                document.getElementById(id).submit();

                            },
                            function () {

                                alertify.error('Eliminación cancelada');
                            }
                        );


                    }
                })
                .catch(() => alertify.error('Error al verificar respuestas del formulario'));
        }

    </script>
@endsection