<div class="modal fade" id="modalSeeder" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-code me-2"></i>
                    <span id="modalSeederTitulo"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <textarea id="seederEditor"></textarea>
            </div>
        </div>
    </div>
</div>




<ul class="list-unstyled ms-2">
    @foreach($items as $item)
        <li class="mb-1">
            @if($item['tipo'] === 'carpeta')
                <i class="fas fa-folder text-warning me-1"></i>
                <strong>{{ $item['nombre'] }}</strong>

                @if(!empty($item['hijos']))
                    @include('seeders.arbol', ['items' => $item['hijos']])
                @endif
            @else
            <div
                class="seeder-item d-flex align-items-center justify-content-between"
                draggable="true"
                data-ruta="{{ $item['ruta'] }}"
                ondragstart="dragSeeder(event)"
            >
                <span class="d-flex align-items-center">
                    <i class="fas fa-file-code text-primary me-1"></i>
                    {{ $item['nombre'] }}
                </span>

                <!-- Ícono para ver seeder -->
                <i
                    class="fas fa-eye text-secondary ms-2"
                    style="cursor: pointer;"
                    onclick="verSeeder('{{ $item['ruta'] }}', '{{ $item['nombre'] }}')"
                    title="Ver seeder"
                ></i>
            </div>
            @endif
        </li>
    @endforeach
</ul>

<script>
let codeMirrorSeeder = null;

document.addEventListener('DOMContentLoaded', () => {
    codeMirrorSeeder = CodeMirror.fromTextArea(
        document.getElementById('seederEditor'),
        {
            mode: "htmlmixed",
            theme: "dracula",
            lineNumbers: true,
            readOnly: true,
            tabSize: 2,
            viewportMargin: Infinity
        }
    );

    codeMirrorSeeder.setSize("100%", "70vh");
});
</script>
<script>
function verSeeder(ruta, nombre) {
    fetch(`{{ route('seeders.ver') }}?ruta=${ruta}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('modalSeederTitulo').innerText = nombre;

            codeMirrorSeeder.setValue(data.contenido);
            codeMirrorSeeder.refresh();

            const modal = new bootstrap.Modal(
                document.getElementById('modalSeeder')
            );
            modal.show();
        });
}
</script>