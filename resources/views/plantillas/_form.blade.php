<!-- CodeMirror 5 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/dracula.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>

<?php
use Illuminate\Support\Facades\Auth;

$usuario_actual = Auth::user()->NombreCompleto;
$fecha_actual = date('d/m/Y');
$hora_actual = date('H:i:s');
$fecha_hora_actual = date('d/m/Y H:i:s');
$mes_actual = date('F'); // nombre literal del mes
$anio_actual = date('Y');
$dia_actual = date('d');
$nombre_sistema = config('app.name');
?>

@csrf

<div class="mb-3">
    <label>Nombre de la plantilla</label>
    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $plantilla->nombre ?? '') }}">
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Estado</label>
    <select name="estado" class="form-select @error('estado') is-invalid @enderror">
        <option value="1" {{ old('estado', $plantilla->estado ?? 1) ? 'selected' : '' }}>Activo</option>
        <option value="0" {{ old('estado', $plantilla->estado ?? 1) ? '' : 'selected' }}>Inactivo</option>
    </select>
    @error('estado')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label>Contenido HTML</label>


    <i class="fas fa-exclamation-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top"
        title="Aquí puedes agregar contenido HTML, clases de Bootstrap, iconos FontAwesome y estilos, pero NO se permite contenido JavaScript ni eventos inline como onclick."></i>
    <!-- Barra de herramientas estilo CKEditor -->
    <div class="editor-toolbar border bg-dark_code px-2 py-2 mb-2">
        <!-- Fila 1: HTML / Estructura -->
        <div class="row g-1 align-items-center mb-1">
            <div class="col-auto d-flex align-items-center flex-wrap">
                <span class="text-light me-2">HTML <i class="fas fa-question-circle" data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="Botones para insertar elementos HTML: div, títulos, texto pequeño, iconos y grid"></i></span>

                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta un div vacío" onclick="insertHTML('div')">Div</button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta un título H4" onclick="insertHTML('h4')">H4</button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta un título H5" onclick="insertHTML('h5')">H5</button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta texto pequeño" onclick="insertHTML('small')">Small</button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta un icono de información" onclick="insertHTML('icon')">Icono</button>
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="Inserta una fila con 2 columnas" onclick="insertHTML('grid')">Grid</button>
            </div>

            <!-- Botón de Vista Previa alineado a la derecha -->
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" onclick="vistaPrevia()">
                    <i class="fas fa-play me-1"></i>Vista Previa
                </button>
            </div>
        </div>

        <div class="row g-1 align-items-center">
            <div class="col-auto d-flex align-items-center flex-wrap">
                <span class="text-light me-2">
                    Variables <i class="fas fa-question-circle" data-bs-toggle="tooltip"
                        title="Botones para insertar variables automáticas como Fecha, Hora, Mes, Año, Día, Usuario, Sistema"></i>
                </span>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $fecha_actual ?>" onclick="insertVariable('fecha_actual')">Fecha</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $hora_actual ?>" onclick="insertVariable('hora_actual')">Hora</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $fecha_hora_actual ?>" onclick="insertVariable('fecha_hora_actual')">Fecha/Hora</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $nombre_sistema ?>" onclick="insertVariable('nombre_sistema')">Nombre Sistema</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $mes_actual ?>" onclick="insertVariable('mes_actual')">Mes</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $anio_actual ?>" onclick="insertVariable('anio_actual')">Año</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $dia_actual ?>" onclick="insertVariable('dia_actual')">Día</button>

                <button type="button" class="btn btn-sm btn-outline-success me-1 py-0 px-1" data-bs-toggle="tooltip"
                    title="<?= $usuario_actual ?>" onclick="insertVariable('usuario_actual')">Usuario</button>
            </div>
        </div>
    </div>

    <textarea id="descripcion" name="contenido" class="form-control @error('contenido') is-invalid @enderror"
        rows="10">{{ $contenido ?? '' }}</textarea>

    @error('contenido')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<!-- Modal de Vista Previa -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div
            class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Vista Previa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let editor;

    document.addEventListener('DOMContentLoaded', function () {
        editor = CodeMirror.fromTextArea(document.getElementById('descripcion'), {
            mode: 'htmlmixed',
            theme: 'dracula',
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
            autoCloseTags: true,
        });

        editor.getWrapperElement().style.fontSize = '13px';
    });

    function vistaPrevia() {
        if (!editor) return;
        let html = editor.getValue();

        // Limpiar scripts y eventos inline
        html = html.replace(/<script[\s\S]*?>[\s\S]*?<\/script>/gi, '');
        html = html.replace(/\s*on\w+="[^"]*"/gi, '');

        const previewDiv = document.getElementById('previewContent');
        previewDiv.innerHTML = html;

        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    }


    function insertHTML(campo) {
        if (!editor) return;
        let html = '';
        switch (campo) {
            case 'div':
                html = '<div></div>\n';
                break;
            case 'h4':
                html = '<h4>Título H4</h4>\n';
                break;
            case 'h5':
                html = '<h5>Título H5</h5>\n';
                break;
            case 'small':
                html = '<small>Texto pequeño</small>\n';
                break;
            case 'icon':
                html = '<i class="fas fa-info-circle"></i>\n';
                break;
            case 'grid':
                html = `<div class="row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6"></div>
                        </div>\n`;
                break;
        }

        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(html, cursor);
        editor.focus();
    }
    function insertVariable(variable) {
        if (!editor) return;

        // Insertamos la variable tal cual entre corchetes
        const valor = `[${variable}]`;

        const doc = editor.getDoc();
        const cursor = doc.getCursor();
        doc.replaceRange(valor, cursor);
        editor.focus();
    }
</script>