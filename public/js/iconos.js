document.addEventListener('DOMContentLoaded', function () {

    const inputIcono = document.getElementById('icono');
    const previewIcono = document.getElementById('preview-icono');
    const inputTitulo = document.getElementById('titulo');

    if (inputIcono && previewIcono) {
        inputIcono.addEventListener('input', function () {
            const valor = inputIcono.value.trim();
            previewIcono.className = valor;
        });
    }

    let debounceTimer;

    if (inputTitulo) {
        inputTitulo.addEventListener('input', function () {

            clearTimeout(debounceTimer);
            const titulo = this.value.trim();

            if (titulo.length < 3) return;

            debounceTimer = setTimeout(() => {

                fetch("/api/sugerir-icono", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({ titulo: titulo })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.icono) {
                            inputIcono.value = data.icono;
                            previewIcono.className = data.icono;
                        }
                    })
                    .catch(error => console.error(error));

            }, 500);

        });
    }

});
