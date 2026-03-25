function mostrarAlerta(tipo, mensaje, opciones = {}) {

    switch (tipo) {

        case 'success':
            alertify.success(mensaje);
        break;

        case 'error':
            alertify.error(mensaje);
        break;

        case 'warning':
            alertify.warning(mensaje);
        break;

        case 'confirm':
            alertify.confirm(
                opciones.titulo || 'Confirmar',
                mensaje,
                function () {
                    if (typeof opciones.onOk === 'function') {
                        opciones.onOk();
                    }
                },
                function () {
                    if (typeof opciones.onCancel === 'function') {
                        opciones.onCancel();
                    } else {
                        alertify.error('Acción cancelada');
                    }
                }
            );
        break;

        default:
            console.warn('Tipo de alerta no soportado');
    }
}