@can($formulario->id . '.eliminar')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('[id^="activar-seleccion-masiva_"]').forEach(btn => {

                const formId = btn.id.split('_').pop();

                const checkTodos = document.getElementById(`check-todos_${formId}`);
                const filaCheckboxes = document.querySelectorAll(`.fila-checkbox_${formId}`);
                const checkCols = document.querySelectorAll(`.check-col_${formId}`);
                const btnEliminar = document.getElementById(`btn-eliminar-masivo_${formId}`);
                const inputIds = document.getElementById(`respuestas_ids_${formId}`);

                let activo = false;

                const actualizarInputIds = () => {
                    const seleccionados = Array.from(filaCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    inputIds.value = seleccionados.join(',');
                };

                btn.addEventListener('click', function () {
                    activo = !activo;

                    if (activo) {
                        btn.classList.replace('btn-outline-secondary', 'btn-primary');
                        btnEliminar.classList.remove('d-none');
                        checkCols.forEach(el => el.classList.remove('d-none'));
                    } else {
                        btn.classList.replace('btn-primary', 'btn-outline-secondary');
                        btnEliminar.classList.add('d-none');
                        checkCols.forEach(el => el.classList.add('d-none'));

                        if (checkTodos) checkTodos.checked = false;
                        filaCheckboxes.forEach(cb => cb.checked = false);
                        btnEliminar.disabled = true;

                        actualizarInputIds();
                    }
                });

                if (checkTodos) {
                    checkTodos.addEventListener('change', function () {
                        filaCheckboxes.forEach(cb => cb.checked = this.checked);
                        btnEliminar.disabled = !this.checked;
                        actualizarInputIds();
                    });
                }

                filaCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        const alguno = Array.from(filaCheckboxes).some(c => c.checked);
                        btnEliminar.disabled = !alguno;

                        if (!this.checked && checkTodos) checkTodos.checked = false;

                        actualizarInputIds();
                    });
                });

            });

        });
    </script>
@endcan