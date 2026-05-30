// CARGA DE CAMPOS

async function cargarCamposDelete(item = null) {

    const formOrigenId =
        document.getElementById('delete-formulario-origen')?.value;

    const formDestinoId =
        document.getElementById('delete-formulario-destino')?.value;

    const items = item
        ? [item]
        : [...document.querySelectorAll('.delete-condicion-item')];

    const promesas = [];

    items.forEach(item => {

        const selectOrigen =
            item.querySelector('.delete-valor-campo');

        const selectDestino =
            item.querySelector('.delete-campo-ref');

        if (formOrigenId && selectOrigen) {

            promesas.push(
                cargarCamposConCache(
                    formOrigenId,
                    selectOrigen,
                    'origen',
                    '-- Seleccione campo origen --'
                )
            );
        }

        if (formDestinoId && selectDestino) {

            promesas.push(
                cargarCamposConCache(
                    formDestinoId,
                    selectDestino,
                    'destino',
                    '-- Seleccione campo destino --'
                )
            );
        }

    });

    await Promise.all(promesas);
}

// VISIBILIDAD SEGUN TIPO

function actualizarTipoValorDelete(item, tipo) {

    const inputEstatico =
        item.querySelector('.delete-valor-estatico');

    const selectOrigen =
        item.querySelector('.delete-valor-campo');

    if (tipo === 'campo') {

        inputEstatico.classList.add('d-none');
        selectOrigen.classList.remove('d-none');

    } else {

        inputEstatico.classList.remove('d-none');
        selectOrigen.classList.add('d-none');
    }
}

// AGREGAR CONDICION

async function agregarCondicionDelete(condicion = null) {
console.log(condicion);
    const container =
        document.getElementById('delete-condiciones-container');

    const template =
        document.getElementById('template-delete-condicion');

    const clon =
        template.content.cloneNode(true);

    container.appendChild(clon);

    const item = container.lastElementChild;

    await cargarCamposDelete(item);

    if (!condicion) {

        actualizarTipoValorDelete(item, 'static');
        return;
    }

    const tipoValor =  condicion.tipo_valor || 'static';
    const selectTipoValor = item.querySelector('.delete-tipo-valor');
    const selectDestino =item.querySelector('.delete-campo-ref');
    const selectOrigen = item.querySelector('.delete-valor-campo');
    const inputEstatico =item.querySelector('.delete-valor-estatico');
    const selectOperador =item.querySelector('.delete-operacion');
    const inputMensaje = item.querySelector('.cond-mensaje');

    selectTipoValor.value = tipoValor;
    selectDestino.value = condicion.campo_condicion_destino || '';
    selectOperador.value = condicion.operador || '';
    inputMensaje.value = condicion.mensaje || '';

    actualizarTipoValorDelete(item, tipoValor);

    if (tipoValor === 'campo') {

        selectOrigen.value =
            condicion.campo_condicion_origen || '';

    } else {

        inputEstatico.value =
            condicion.valor || '';
    }
}

// EVENTOS INICIALES

document.getElementById('btn-add-delete-condicion')
    ?.addEventListener('click', () => agregarCondicionDelete());

document.getElementById('delete-formulario-origen')
    ?.addEventListener('change', () => cargarCamposDelete());

document.getElementById('delete-formulario-destino')
    ?.addEventListener('change', () => cargarCamposDelete());

// EVENTOS DINAMICOS

document.addEventListener('change', e => {

    if (!e.target.classList.contains('delete-tipo-valor')) {
        return;
    }

    const item =
        e.target.closest('.delete-condicion-item');

    actualizarTipoValorDelete(
        item,
        e.target.value
    );

});

// ELIMINAR

document.addEventListener('click', e => {

    const btn =
        e.target.closest('.btn-remove-delete-condicion');

    if (!btn) {
        return;
    }

    btn.closest('.delete-condicion-item').remove();

});