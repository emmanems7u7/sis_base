document.querySelectorAll('.btn-exportar').forEach(btn => {

    btn.addEventListener('click', function (e) {

        e.preventDefault();

        const urlBase = this.dataset.url;
        const formId = this.dataset.formId?.trim();
        const formActivo = this.dataset.formActivo?.trim();

        const params = new URLSearchParams(window.location.search);

        const tieneFiltros = [...params.values()]
            .some(valor => valor && valor.trim() !== '');

        // No hay filtros
        if (!tieneFiltros) {

            window.open(urlBase, '_blank');

            return;
        }

        if (!formActivo) {

            window.open(urlBase, '_blank');

            return;
        }

        if (formId !== formActivo) {

            window.open(urlBase, '_blank');

            return;
        }

        mostrarAlerta(
            'confirm',
            'Se detectaron filtros aplicados. ¿Desea exportar únicamente los registros filtrados?',
            {
                titulo: 'Exportación',

                onOk: () => {

                    const urlFiltrada = `${urlBase}?${params.toString()}`;

                    window.open(urlFiltrada, '_blank');
                },

                onCancel: () => {

                   mostrarAlerta('info', 'Exportación cancelada.');
                }
            }
        );
    });
});