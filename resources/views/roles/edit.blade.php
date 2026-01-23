@extends('layouts.argon')

@section('content')

    <style>
        @keyframes animarBorde {

            0%,
            100% {
                border-color: transparent;
            }

            50% {
                border-color: rgba(0, 123, 255, 0.8);
            }
        }

        .borde-animado {
            border: 2px solid transparent;
            border-radius: 8px;
            animation: animarBorde 2s infinite;
        }
    </style>
    <script>
        function toggleMenu(id) {
            const div = document.getElementById('menuc_' + id);
            div.style.display = div.style.display === 'none' ? 'block' : 'none';
        }
    </script>

    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')


        <div class="row">

<div class="col-md-6 mt-2 order-1 order-md-2">
    <div class="card shadow-lg">
        <div class="card-body">
            <h5>
                <i class="fas fa-user-shield me-2"></i>
                Editar Rol y Asignación de Accesos
            </h5>

            <small class="d-block mb-2">
                <i class="fas fa-plus-circle me-1"></i>
                En este módulo puedes <strong>Editar un rol</strong> del sistema.
            </small>

            <small class="d-block mb-2">
                <i class="fas fa-sitemap me-1"></i>
                Después de verificar el nombre del rol, asigna <strong>secciones y menús dinámicos</strong> a las
                que tendrá
                acceso.
            </small>

            <small class="d-block mb-2">
                <i class="fas fa-key me-1"></i>
                También puedes asignar <strong>permisos específicos</strong> disponibles en el sistema para este
                rol.
            </small>


        </div>
    </div>
</div>

<div class="col-md-6 order-2 order-md-1 mt-2">
    <div class="card shadow-lg">
        <div class="card-body">
            <h5>Acciones Disponibles</h5>


            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary "><i
                    class="fas fa-arrow-left me-1"></i>Volver</a>


            <button type="submit" class="btn btn-sm btn-primary">Guardar Rol</button>

            <div class="form-group">
                <label for="roleName">Nombre del rol</label>
                <input type="text" 
                name="name" 
                id="roleName"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="Ingrese el nombre del rol"
                value="{{ old('name', $role->name ?? '') }}">
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>
    </div>
</div>

</div>







        <div class="row mt-3">
            {{-- Columna izquierda: permisos que NO son tipo "permiso" --}}
            <div class="col-md-4">
                <div class="card shadow-xl">
                    <div class="card-body">
                        <h5>Accesos al Menú Dinámico</h5>

                        @foreach ($permisosPorTipo['seccion'] as $seccion)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="permissions[]"
                                    value="{{ $seccion->name }}" {{ $seccion->checked ? 'checked' : '' }}
                                    onclick="toggleMenu('{{ $seccion->id }}')">

                                <label class="form-check-label">
                                    <strong>{{ $seccion->name }}</strong>
                                </label>

                                <div id="menuc_{{ $seccion->id }}"
                                    style="{{ $seccion->checked ? '' : 'display:none;' }} font-size: 0.95em;">

                                    @foreach ($seccion->menus as $menu)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="{{ $menu->name }}" {{ $menu->checked ? 'checked' : '' }}>

                                            <label class="form-check-label">
                                                {{ $menu->name }}
                                            </label>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            <div class="col-md-8">

            <div class="card shadow-xl">
                <div class="card-body">
                    
               
                @if (isset($permisosPorTipo['permiso']) && $permisosPorTipo['permiso']->count() > 0)
                <h5>Permisos Disponibles en el sistema</h5>

                @php
                    $permisosPorSeccion = $permisosPorTipo['permiso']->groupBy(function ($permiso) {
                        return explode('.', $permiso->name)[0];
                    });
                @endphp

                {{-- Contenedor de 2 columnas de secciones --}}
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    @foreach ($permisosPorSeccion as $seccion => $permisos)
                        <div class="col">
                            <div class="border-bottom pb-2">
                                <h6 class="mb-2 text-muted">{{ ucfirst($seccion) }}</h6>

                                {{-- Permisos distribuidos horizontalmente --}}
                                <div class="d-flex flex-wrap" id="seccion_permiso_{{ $seccion }}">
                                    @foreach ($permisos as $permiso)
                                        <div class="form-check mb-2 me-3" style="min-width: 150px; flex: 0 0 auto; border-start: 2px solid #dee2e6;">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permiso->name }}"
                                                id="permiso_{{ $permiso->id }}"
                                                {{ $role->hasPermissionTo($permiso) ? 'checked' : '' }}
                                                onclick="verificarPermiso(this, '{{ $permiso->id }}', '{{ $role->id }}')">

                                            <label class="form-check-label" for="permiso_{{ $permiso->id }}">
                                                {{ $permiso->name }}
                                            </label>

                                            <div id="menuc_{{ $permiso->id }}" class="ps-3 mt-1">
                                                <div id="menu_{{ $permiso->id }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

                </div>
            </div>
            </div>


        </div>

        


    </form>





@endsection