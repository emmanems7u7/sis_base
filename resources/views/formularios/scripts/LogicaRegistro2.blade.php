<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.addEventListener('change', function (e) {

            const select = e.target;

            if (!select.classList.contains('campo-dinamico')) return;

            const form = select.closest('form');

            if (!form || !form.querySelector('.campo-relacion')) return;

            const valorSeleccionado = select.value;
            const campoId = select.dataset.campoId;
            const nombreCampo = select.name;

            if (!valorSeleccionado) return;

            fetch("{{ route('campos.obtenerData') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content")
                },
                body: JSON.stringify({
                    campo_id: campoId,
                    nombre: nombreCampo,
                    valor: valorSeleccionado
                }),
                showOverlay: false
            })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) return;

                    form.querySelectorAll(`[data-campo-id="${data.campo_referencia}"]`)
                        .forEach(input => {

                            if (input.type === 'radio') {
                                input.checked = input.value == data.valor;
                            } else {
                                input.value = data.valor ?? '';
                            }

                        });

                })
                .catch(err => console.error('Error:', err));

        });

    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('input[type="file"]').forEach(input => {

            input.addEventListener('change', function (e) {

                const file = e.target.files[0];
                const previewId = e.target.dataset.preview;
                const previewContainer = document.getElementById(previewId);

                if (!file || !previewContainer) return;

                previewContainer.innerHTML = '';
                const reader = new FileReader();

                reader.onload = function (event) {

                    if (file.type.startsWith('image/')) {

                        previewContainer.innerHTML = `
                        <img src="${event.target.result}"
                             class="img-thumbnail"
                             style="max-height:150px;">
                    `;

                    } else if (file.type.startsWith('video/')) {

                        previewContainer.innerHTML = `
                        <video controls style="max-width:100%; max-height:200px">
                            <source src="${event.target.result}">
                        </video>
                    `;

                    } else {

                        previewContainer.innerHTML = `
                        <a href="${event.target.result}"
                           target="_blank"
                           class="btn btn-outline-secondary btn-sm">
                           Ver archivo seleccionado
                        </a>
                    `;
                    }
                };

                reader.readAsDataURL(file);
            });
        });

    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        inicializarCamposAutomaticos();
    });

    function inicializarCamposAutomaticos() {

        const inputs = document.querySelectorAll('[data-tipo="identificador"], [data-tipo="fecha"], [data-tipo="hora"]');

        let campos = [];

        inputs.forEach(input => {

            if (input.dataset.autogenerado === 'true') return;

            if (input.dataset.caso === 'store') {

                campos.push({
                    campo_id: input.dataset.campoId
                });

                input.dataset.autogenerado = 'true';
            }
        });

        if (campos.length === 0) return;

        fetch("{{ route('campo.generar') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ campos }),
            showOverlay: false

        })
            .then(res => res.json())
            .then(data => {

                data.forEach(item => {
                    let input = document.querySelector(`[data-campo-id="${item.campo_id}"]`);
                    if (input) input.value = item.valor;
                });

            });
    }

</script>