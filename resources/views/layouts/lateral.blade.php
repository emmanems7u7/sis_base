<ul class="navbar-nav">
            <li class="nav-item d-flex flex-column align-items-center">

                @if (Auth::user()->foto_perfil)
                <img src="{{ asset(Auth::user()->foto_perfil) }}" alt="Foto de perfil" class="rounded-circle" style="width: 115px; height: 115px; object-fit: cover;">
                @else
                <img src="{{ asset('update/imagenes/user.jpg') }}" alt="Foto de perfil" class="rounded-circle" style="width: 115px; height: 115px; object-fit: cover;">             
                @endif

                <p class="ps-3 ms-3 nav-link-text ms-1" style="font-size: 14px; text-align: center;">
                        {{ Auth::user()->usuario_nombres }} {{ Auth::user()->usuario_app }} {{ Auth::user()->usuario_apm }}
                </p>
            </li>

                @foreach(Auth::user()->roles as $role) 
                            <p class="ps-3 ms-3 nav-link-text ms-1" style="font-size: 12px;">
                                {{$role->name;}}

                            </p>
                 @endforeach

        
            <li class="nav-item  text-black">
                <a class="nav-link active" href="{{ route('home') }}">
                    <span class="ps-2   nav-link-text ms-1 text-black">Inicio</span>
                </a>
            </li>
                
            <li class="nav-item">
                 <a class="nav-link" href="{{ route('user.actualizar.contraseña') }}">
                    <span class="ps-2  nav-link-text ms-1  text-black">Actualizar contraseña</span>
                 </a>
            </li>

            @if( $tiempo_cambio_contraseña != 1)
     
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('perfil') }}">
                        <span class="ps-2  nav-link-text ms-1  text-black">Perfil</span>
                    </a>
                </li>
                @role('admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('menus.index') }}">

                        <span class="ps-2  nav-link-text ms-1  text-black">Gestión de menus</span>
                    </a>
                </li>
                @endrole

             

                <ul id="secciones-list" class="list-unstyled" {{ $configuracion->mantenimiento ? 'data-draggable="false"' : 'data-draggable="true"' }}>
                    @foreach ($secciones as $seccion)
                        @can($seccion->titulo)
                            <li class="seccion-item p-2 text-black" data-id="{{ $seccion->id }}">
                                <div class=" text-black d-flex align-items-center {{ $configuracion->mantenimiento ? 'text-warning' : '' }}">
                                    <i class="{{ $seccion->icono }} me-2"></i>
                                    <h6 class=" text-black m-0 text-uppercase text-xs font-weight-bolder  {{ $configuracion->mantenimiento ? 'text-warning' : '' }}">{{ $seccion->titulo }}</h6>
                                </div>

                                <ul class="list-unstyled ms-2">
                                @foreach ($seccion->menus as $menu)
                                    @can($menu->nombre)
                                        <li class="nav-item text-black">
                                            @php
                                                if($menu->modulo_id == null || $menu->modulo_id == 0){
                                                    $ruta = route($menu->ruta);
                                                    $esActivo = Route::currentRouteName() === $menu->ruta;
                                                } else {
                                                    $ruta = route('modulo.index', ['modulo_id' => $menu->modulo_id]);

                                                    // Compara nombre de ruta + parámetro modulo_id
                                                    $esActivo = Route::currentRouteName() === 'modulo.index' 
                                                        && request()->route('modulo_id') == $menu->modulo_id;
                                                }
                                            @endphp

                                            <a class="nav-link {{ $esActivo ? 'active bg-gradient-' . $color : '' }}" href="{{ $ruta }}">
                                                <span class="text-black nav-link-text">{{ $menu->nombre }}</span>
                                            </a>
                                        </li>
                                    @endcan
                                @endforeach
                                </ul>
                            </li>
                        @endcan
                    @endforeach
                </ul>
        @endif

        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}"onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
               <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="fas fa-sign-out-alt text-dark text-sm opacity-10"></i>
               </div>
                <span class="nav-link-text ms-1 text-blackv">Salir</span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>

        </li>
           
</ul>