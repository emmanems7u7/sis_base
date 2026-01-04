<!-- Modal -->
<div class="modal fade" id="modal_busqueda_{{ $formulario->id }}" tabindex="-1" role="dialog" aria-labelledby="modal_busquedaLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div
            class="modal-content  {{ auth()->user()->preferences && auth()->user()->preferences->dark_mode ? 'bg-dark text-white' : 'bg-white text-dark' }}">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_busquedaLabel"><i class="fas fa-filter"></i> Filtrar Resultados</h5>
                <button type="button" class="btn-close txt-black" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">

                    @if($modulo == 0)

                        <form action="{{ route('formularios.respuestas.formulario', $formulario->id) }}" method="GET">
                    
                    @else
                        <form action="{{ route('modulo.index',  $modulo) }}" method="GET">
                           
                    @endif
                     
                        <div class="input-group">

                                @include('formularios._campos', ['cols' => 1, 'requerido' => 0])


                            </div>



                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn bg-gradient-primary">Filtrar</button>
            </div>
            </form>

        </div>
    </div>
</div>