<!-- API KEY IA GROQ - Múltiples Tokens -->
<div class="mb-3">
    <label class="form-label">API KEY IA GROQ</label>

    <!-- Botón para añadir nuevo token -->
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-success" id="addTokenBtn">
            <i class="fas fa-plus"></i> Añadir Token
        </button>
    </div>

    <!-- Tabla para mostrar los tokens añadidos -->
    <div class="table-responsive">
        <table class="table table-bordered" id="tokensTable">
            <thead>
                <tr>
                    <th>Token</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($tokens))
                    @foreach($tokens as $token)
                        <tr>
                            <td>
                                <input type="text" name="tokens[{{ $loop->index }}][token]" class="form-control"
                                    value="{{ $token->token }}" required>
                            </td>
                            <td>
                                <!-- Campo solo lectura -->
                                <input type="text" class="form-control"
                                    value="{{ ucfirst($token->estado == 1 ? 'Vigente' : 'En Recuperación') }}" readonly>
                                <!-- Campo hidden para enviar al backend -->
                                <input type="hidden" name="tokens[{{ $loop->index }}][estado]" value="{{ $token->estado  }}">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger removeTokenBtn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        let tokenIndex = {{ isset($tokens) ? count($tokens) : 0 }};
        const tokensTable = document.querySelector('#tokensTable tbody');
        const addTokenBtn = document.querySelector('#addTokenBtn');

        addTokenBtn.addEventListener('click', function () {
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <input type="text" name="tokens[${tokenIndex}][token]" class="form-control" required>
                </td>
                <td>
                    <input type="text" class="form-control" value="token" readonly>
                    <input type="hidden" name="tokens[${tokenIndex}][estado]" value="activo">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger removeTokenBtn">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tokensTable.appendChild(newRow);
            tokenIndex++;
        });

        // Eliminar fila
        tokensTable.addEventListener('click', function (e) {
            if (e.target.closest('.removeTokenBtn')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>