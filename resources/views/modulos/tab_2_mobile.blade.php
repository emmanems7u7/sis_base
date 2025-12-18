<div class="row g-3">
    @foreach($rules as $rule)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-lg">
                <div class="card-body">
                    <h5 class="card-title">{{ $rule->nombre }}</h5>
                    <p class="mb-1"><strong>Formulario Origen:</strong> {{ $rule->formulario->nombre }}</p>
                    <p class="mb-1"><strong>Evento:</strong> {{ $rule->evento }}</p>

                    @if($rule->actions->count())
                        <div class="mt-2">
                            <strong>Acciones:</strong>
                            <ul class="list-group list-group-flush">
                                @foreach($rule->actions as $act)
                                    <li class="list-group-item p-1">
                                        {{ $act->OperacionCatalogo }} →
                                        {{ $act->formularioDestino->nombre ?? 'Sin formulario' }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('form-logic.edit', ['rule' => $rule->id, 'modulo' => $modulo->id]) }}"
                        class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('form-logic.delete', $rule->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>