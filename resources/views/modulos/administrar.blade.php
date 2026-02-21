@extends('layouts.argon')

@section('content')





    <div class="row">

        <div class="col-md-6 mt-2 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>
                        <i class="fas fa-sitemap me-2"></i>
                        Administracion de Módulos dinámicos
                    </h5>

                    <small>
                        <i class="fas fa-project-diagram me-1"></i>
                        En este apartado puede definir la <strong>lógica del módulo</strong> y la relación entre los módulos
                        del sistema.
                    </small><br>

                    <small>
                        <i class="fas fa-link me-1"></i>
                        Permite configurar cómo se asocian los formularios y cómo interactúan entre sí dentro del módulo.
                    </small><br>

                    <small>
                        <i class="fas fa-eye me-1"></i>
                        Puede establecer qué formularios serán visibles y definir el <strong>tipo de visualización</strong>
                        según su configuración.
                    </small><br>

                    <small>
                        <i class="fas fa-sliders-h me-1 text-primary"></i>
                        Ajuste el comportamiento dinámico del módulo para personalizar la experiencia del usuario.
                    </small><br>


                </div>
            </div>
        </div>

        <div class="col-md-6 order-2 order-md-1 mt-2">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Administrar Módulo: {{ $modulo->nombre }}</h5>
                </div>
            </div>
        </div>

    </div>


    <div class="card mt-2 shadow-lg">
        <div class="card-body">

            <!-- Tabs de administración de módulo -->
            <ul class="nav nav-tabs" id="moduloTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button"
                        role="tab" aria-controls="info" aria-selected="true">
                        <i class="fas fa-info-circle me-1"></i> Información del Módulo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logica-tab" data-bs-toggle="tab" data-bs-target="#logica" type="button"
                        role="tab" aria-controls="logica" aria-selected="false">
                        <i class="fas fa-cogs me-1"></i> Lógica del Módulo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="asociacion-tab" data-bs-toggle="tab" data-bs-target="#asociacion"
                        type="button" role="tab" aria-controls="asociacion" aria-selected="false">
                        <i class="fas fa-link me-1"></i> Asociación de Formularios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="diagrama-tab" data-bs-toggle="tab" data-bs-target="#diagrama" type="button"
                        role="tab" aria-controls="diagrama" aria-selected="false">
                        <i class="fas fa-project-diagram me-1"></i> Personalización del Módulo
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4 border border-top-0 rounded-bottom " id="moduloTabContent">
                <!-- Información del Módulo -->
                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                    @include('modulos.tab1')
                </div>

                <!-- Lógica del Módulo -->
                <div class="tab-pane fade" id="logica" role="tabpanel" aria-labelledby="logica-tab">
                    @include('modulos.tab2')

                </div>

                <!-- Asociación de Módulos -->
                <div class="tab-pane fade" id="asociacion" role="tabpanel" aria-labelledby="asociacion-tab">
                    @include('modulos.tab3')

                </div>

                <!-- Diagrama de Flujo -->
                <div class="tab-pane fade" id="diagrama" role="tabpanel" aria-labelledby="diagrama-tab">
                    @include('modulos.tab4')

                </div>
            </div>

        </div>
    </div>

@endsection