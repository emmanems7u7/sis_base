async function cambiarTipoValor(valorSeleccionado = null) {

    const tipo = document.getElementById('tipo-valor').value;
    const inputEstatico = document.getElementById('modal-valor-estatico');
    const selectCampo = document.getElementById('modal-valor-campo');
    const formId = document.getElementById('formulario_id').value;

    if (tipo === 'campo') {

        inputEstatico.classList.add('d-none');
        selectCampo.classList.remove('d-none');

        await cargarCamposCached(
            formId,
            selectCampo,
            '-- Seleccione campo --'
        );

        if (valorSeleccionado) {
            selectCampo.value = valorSeleccionado;

        }

    } else {

        inputEstatico.classList.remove('d-none');
        selectCampo.classList.add('d-none');

    }
}
  
  /*PARA TAC-001*/
  document.getElementById('tipo-valor')
  .addEventListener('change', () => {
      cambiarTipoValor();
  });
