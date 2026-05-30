function crearBotonIteracion(texto, marcador,contenedorMultiples) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-primary btn-sm campo-btn me-1 mb-1';
    btn.textContent = texto;

    btn.addEventListener('click', () => {
        insertarTextoEnEditor(editor, `[${marcador}]`);
    });

    contenedorMultiples.appendChild(btn);
}

async function obtenerCamposUsuario() {
    const res = await fetch('/email/campos-usuario');
    const json = await res.json();
    return json.data || [];
}

async function cargarCamposOrigenParaEmail() {
    const formOrigenId = formularioDisparador.value;
    const contenedor = document.getElementById('email-campos-origen');
    const textarea = document.getElementById('modal-email-body');

    if (!formOrigenId || !contenedor) return;

    contenedor.innerHTML = '<span class="text-muted small">Cargando campos...</span>';
    const contenedor_usuarios = document.getElementById('email-campos-usuarios');


    contenedor_usuarios.innerHTML = '';
    var camposUsuario = await obtenerCamposUsuario();

    /* =========================
        CAMPOS DE USUARIO
     ========================= */
    camposUsuario.forEach(campo => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-secondary btn-xs campo-btn';
        btn.textContent = campo.label;

        btn.addEventListener('click', () => {
            insertarTextoEnEditor(editor, `[${campo.nombre}]`);

        });

        contenedor_usuarios.appendChild(btn);
    });




    // Reutiliza cache existente
    await cargarCamposCached(formOrigenId, document.createElement('select'), '--');

    const campos = camposCache[formOrigenId] || [];
    contenedor.innerHTML = '';



    if (!campos.length) {
        contenedor.innerHTML = '<span class="text-muted small">Sin campos disponibles</span>';
        return;
    }

    // Contenedor para campos normales
    const contenedorNormales = document.createElement('div');
    contenedorNormales.className = 'mb-3';

    // Contenedor para múltiples
    const contenedorMultiples = document.createElement('div');
    contenedorMultiples.className = 'mt-3';

    // ===== CAMPOS NORMALES =====
    campos.forEach(campo => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-secondary btn-xs campo-btn me-1 mb-1';
        btn.textContent = campo.nombre;

        btn.addEventListener('click', () => {
            insertarTextoEnEditor(editor, `[${campo.nombre}]`);
        });

        contenedorNormales.appendChild(btn);
    });

    // Agregar normales arriba
    contenedor.appendChild(contenedorNormales);


    //  TÍTULO REGISTROS MÚLTIPLES 
    const tituloMultiple = document.createElement('div');
    tituloMultiple.className = 'fw-bold text-primary mt-3 mb-2';
    tituloMultiple.textContent = 'opciones para mostrar registros múltiples';
    contenedorMultiples.appendChild(tituloMultiple);

    crearBotonIteracion('Mostrar en tabla', 'iterar_tabla',contenedorMultiples);
    crearBotonIteracion('Mostrar como lista', 'iterar_lista',contenedorMultiples);
    crearBotonIteracion('Mostrar en párrafos', 'iterar_parrafos',contenedorMultiples);


    // Agregar bloque múltiple debajo de los normales
    contenedor.appendChild(contenedorMultiples);


}



// Función para insertar texto en CKEditor
function insertarTextoEnEditor(editorInstance, texto) {
    if (!editorInstance) return; // seguridad
    editorInstance.model.change(writer => {
        const selection = editorInstance.model.document.selection;
        const position = selection.getFirstPosition();
        writer.insertText(texto, position);
    });
    editorInstance.editing.view.focus();
}