<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/apple-icon.png')  }}">
    <link rel="icon" type="image/png" href="{{ asset('logo.png')  }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('argon/css/argon-dashboard.css?v=2.1.0')  }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

   

    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

   
    @vite(['resources/js/app.js'])
    
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

    @php
        use App\Models\Seccion;
        use Carbon\Carbon;
        use App\Models\ConfiguracionCredenciales;
        use App\Models\Configuracion;
        use App\Models\UserPersonalizacion;
        $secciones = Seccion::with('menus')->orderBy('posicion')->get();
        $config = ConfiguracionCredenciales::first();
        $configuracion = Configuracion::first();
       
    $user = auth()->user();

    if (Schema::hasTable('user_personalizacions')) {
    $preferencias = UserPersonalizacion::where('user_id', $user->id)->first();
        } else {
            $preferencias = null;
        }
       
        if (Auth::user()->usuario_fecha_ultimo_password) {
            $ultimoCambio = Carbon::parse(Auth::user()->usuario_fecha_ultimo_password);

            $diferenciaDias = (int) $ultimoCambio->diffInDays(Carbon::now());

            if ($diferenciaDias >= $config->conf_duracion_max) {
                $tiempo_cambio_contraseña = 1;
            } else {
                $tiempo_cambio_contraseña = 2;
            }
        } else {
            $tiempo_cambio_contraseña = 1;
        }

    @endphp
</head>
 

<style>
   

#loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>

<div id="loader" style="display: none;">
    <div id="loader-spinner" class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<body class="{{ isset($preferencias) && $preferencias->dark_mode ? 'dark-version' : '' }} g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-green_fondo  text-black position-absolute w-100"></div>
    <aside
        class="sidenav {{ isset($preferencias) ? $preferencias->sidebar_type : 'bg-white' }} navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand m-0" href="{{ route('home') }}">
             <img src="{{ asset('logo.png')}}" style="" alt="">
                <span class="ms-1 font-weight-bold">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>
        
        <div class="collapse  text-black navbar-collapse w-auto" id="sidenav-collapse-main">
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

                        <span class="ps-3 ms-3 nav-link-text ms-1 text-black">Inicio</span>
                    </a>
                </li>
                
                <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.actualizar.contraseña') }}">

                            <span class="ps-3 ms-3 nav-link-text ms-1  text-black">Actualizar contraseña</span>
                        </a>
                </li>
            @if( $tiempo_cambio_contraseña != 1)
     
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('perfil') }}">
                        <span class="ps-3 ms-3 nav-link-text ms-1  text-black">Perfil</span>
                    </a>
                </li>
                @role('admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('menus.index') }}">

                        <span class="ps-3 ms-3 nav-link-text ms-1  text-black">Gestión de menus</span>
                    </a>
                </li>
                @endrole

                @php

                     $color = 'primary'; // valor por defecto
                 
                    if (Schema::hasTable('user_personalizacions') && Auth::check()) {
                        $user = Auth::user();

                        if (method_exists($user, 'preferences') && $user->preferences) {
                            $color = $user->preferences->sidebar_color ?? 'primary';
                        }
                    }
                @endphp

                <ul id="secciones-list" class="list-unstyled" {{ $configuracion->mantenimiento ? 'data-draggable="false"' : 'data-draggable="true"' }}>
                    @foreach ($secciones as $seccion)
                        @can($seccion->titulo)
                            <li class="seccion-item mb-3 p-2 text-black" data-id="{{ $seccion->id }}">
                                <div class=" text-black d-flex align-items-center {{ $configuracion->mantenimiento ? 'text-warning' : '' }}">
                                    <i class="{{ $seccion->icono }} me-2"></i>
                                    <h6 class=" text-black m-0 text-uppercase text-xs font-weight-bolder  {{ $configuracion->mantenimiento ? 'text-warning' : '' }}">{{ $seccion->titulo }}</h6>
                                </div>

                                <ul class="list-unstyled ms-4 mt-2">
                                    @foreach ($seccion->menus as $menu)
                                        @can($menu->nombre)
                                            <li class="nav-item text-black">
                                                <a class="nav-link {{ Route::currentRouteName() === $menu->ruta ? 'active bg-gradient-' . $color : '' }}" href="{{ route($menu->ruta) }}">
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
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-sign-out-alt text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1 text-blackv">Salir</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
        </li>
           
</ul>
        </div>
<!-- CDN de SortableJS -->
@if($configuracion->mantenimiento == 1)
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const lista = document.getElementById('secciones-list');

    new Sortable(lista, {
        animation: 150,
        onEnd: function () {
            const orden = Array.from(document.querySelectorAll('.seccion-item'))
                .map((el, index) => ({
                    id: el.dataset.id,
                    posicion: index + 1
                }));

            fetch('{{ route("secciones.ordenar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orden })
            }).then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alertify.success(data.message);
                } else {
                    alertify.error(data.message || 'Ocurrió un error al ordenar');
                }
            })
        }
    });


</script>
@endif
    </aside>
     
    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur"
            data-scroll="false">
            <div class="container-fluid py-1 px-3">

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        @foreach ($breadcrumb as $key => $crumb)
                            @if ($key == count($breadcrumb) - 1)
                                
                                <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{ $crumb['name'] }}</li>
                            @else
                             
                                <li class="breadcrumb-item text-sm">
                                    <a class="opacity-5 text-white" href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                    
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                            
                        </div>
                        <ul class="navbar-nav  justify-content-end">
                            
                            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                                <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                                    <div class="sidenav-toggler-inner">
                                        <i class="sidenav-toggler-line bg-white"></i>
                                        <i class="sidenav-toggler-line bg-white"></i>
                                        <i class="sidenav-toggler-line bg-white"></i>
                                    </div>
                                </a>
                            </li>

                            <li class="nav-item px-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-white p-0">
                                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                            </a>
                        </li>
                        <li class="nav-item px-3 d-flex align-items-center">

<div class="notification-wrapper">
@php
    use Illuminate\Support\Facades\Schema;

    $tieneNotificaciones = false;
    $cantidadNotificaciones = 0;

    if (Schema::hasTable('notifications') && Auth::check()) {
        $cantidadNotificaciones = Auth::user()->unreadNotifications->count();
        $tieneNotificaciones = $cantidadNotificaciones > 0;
    }
@endphp

<div id="notificationTrigger"
     class="notification-icon {{ $tieneNotificaciones ? 'has-notifications' : '' }}">
    <i class="fas fa-bell text-warning"></i>
    @if($tieneNotificaciones)
        <span class="badge">{{ $cantidadNotificaciones }}</span>
    @endif
</div>


    <div id="notificationBox" 
        class="notification-box {{ isset($preferencias) && $preferencias->dark_mode ? 'dark-version' : '' }}">

        <ul>
        @php
       

            $notificaciones = collect();
            if (Schema::hasTable('notifications') && Auth::check()) {
                $notificaciones = Auth::user()->unreadNotifications;
            }
        @endphp

        @forelse($notificaciones as $notification)
            <li class="list-group-item">
                <a style="text-decoration: none;"
                onclick="NotificacionLeida(event,'{{ $notification->id }}')"
                href="{{ $notification->data['action_url'] }}"
                class="text-black float-right">
                    <strong>{{ $notification->created_at->diffForHumans() }}</strong> -
                    {{ $notification->data['message'] }}
                </a>
            </li>
        @empty
            <li>No hay notificaciones nuevas</li>
        @endforelse

        </ul>
    </div>
</div>
</li>
                        
                        </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
        <div class="container">

       
        <script>
    document.addEventListener('DOMContentLoaded', function () {
        alertify.set('notifier', 'position', 'top-right');

        @foreach (['status' => 'success', 'error' => 'error', 'warning' => 'warning'] as $msg => $type)
            @if(session($msg))
                alertify.{{ $type }}(@json(session($msg)));
            @endif
        @endforeach

        @if($errors->any())
            @foreach ($errors->all() as $error)
                alertify.error(@json($error));
            @endforeach
        @endif
    });
        </script>
      
        @yield('content')
      
          
            
        </div>

  
    </main>


    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"> </i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Personaliza como quieres ver el sistema</h5>

                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
                <!-- End Toggle Button -->
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <!-- Sidebar Backgrounds -->
                <div>
                    <h6 class="mb-0">Color de selector en menu</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger"
                            onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <!-- Sidenav Type -->
                <div class="mt-3">
                    <h6 class="mb-0">Tipo de menu lateral</h6>
                    <p class="text-sm">Puedes seleccionar entre 2 tipos</p>
                </div>
                <div class="d-flex">
                    <button
                        class="btn bg-gradient-primary w-100 px-3 mb-2 {{ isset($preferencias) && $preferencias->sidebar_type == 'bg-white' ? 'active' : '' }}
"
                        data-class="bg-white" onclick="sidebarType(this)">Claro</button>
                    <button
                        class="btn bg-gradient-primary w-100 px-3 mb-2 {{ isset($preferencias) && $preferencias->sidebar_type == 'bg-default' ? 'active' : '' }}"
                        data-class="bg-default" onclick="sidebarType(this)">Oscuro</button>


                </div>
                <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
                <!-- Navbar Fixed -->
                <div class="d-flex my-3">
                    <h6 class="mb-0">Cabecera de Rutas</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed"
                            onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Claro / Oscuro</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version"
                            onclick="darkMode(this)" {{ isset($preferencias) && $preferencias->dark_mode ? 'checked' : '' }}>

                    </div>
                </div>

            </div>
        </div>
    </div>

  
    <script>
        function sidebarColor(a) {
          
            var parent = document.querySelector(".nav-link.active");
            var color = a.getAttribute("data-color");

            // Limpiar clases anteriores
            [
                'primary', 'dark', 'info', 'success', 'warning', 'danger'
            ].forEach(function (c) {
                parent.classList.remove('bg-gradient-' + c);
            });

            // Agregar nuevo color
            parent.classList.add('bg-gradient-' + color);

            // Marcar badge activo
            document.querySelectorAll('.badge.filter').forEach(function (el) {
                el.classList.remove('active');
            });
            a.classList.add('active');

            // Guardar en backend
            fetch('/guardar-color-sidebar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ color: color })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Color guardado con éxito');
                    }
                });
        }


        function sidebarType(e) {
            const selectedType = e.getAttribute("data-class");
            // Enviar al backend con fetch/AJAX
            fetch('/user/personalizacion/sidebar-type', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ sidebar_type: selectedType }),
            }).then(res => {
                if (!res.ok) throw new Error('Error al guardar personalización');
                return res.json();
            }).then(data => {
                console.log('Guardado con éxito');
            }).catch(err => {
                console.error(err);
            });
            for (
                var t = e.parentElement.children, s = e.getAttribute("data-class"),
                n = document.querySelector("body"),
                a = document.querySelector("body:not(.dark-version)"),
                n = n.classList.contains("dark-version"),
                i = [], r = 0;
                r < t.length; r++)t[r].classList.remove("active"), i.push(t[r].getAttribute("data-class")); e.classList.contains("active") ? e.classList.remove("active") : e.classList.add("active"); for (var l, o, c, d = document.querySelector(".sidenav"), r = 0; r < i.length; r++)d.classList.remove(i[r]); if (d.classList.add(s), "bg-transparent" == s || "bg-white" == s) { var u = document.querySelectorAll(".sidenav .text-white:not(.nav-link-text):not(.active)"); for (let e = 0; e < u.length; e++)u[e].classList.remove("text-white"), u[e].classList.add("text-dark") } else { var f = document.querySelectorAll(".sidenav .text-dark"); for (let e = 0; e < f.length; e++)f[e].classList.add("text-white"), f[e].classList.remove("text-dark") } if ("bg-transparent" == s && n) { f = document.querySelectorAll(".navbar-brand .text-dark"); for (let e = 0; e < f.length; e++)f[e].classList.add("text-white"), f[e].classList.remove("text-dark") } "bg-transparent" != s && "bg-white" != s || !a ? (o = (l = document.querySelector(".navbar-brand-img")).src).includes("logo-ct-dark.png") && (c = o.replace("logo-ct-dark", "logo-ct"), l.src = c) : (o = (l = document.querySelector(".navbar-brand-img")).src).includes("logo-ct.png") && (c = o.replace("logo-ct", "logo-ct-dark"), l.src = c), "bg-white" == s && n && (o = (l = document.querySelector(".navbar-brand-img")).src).includes("logo-ct.png") && (c = o.replace("logo-ct", "logo-ct-dark"), l.src = c)




        }
    </script>


    <script>
        function darkMode(el) {
            var check;
            if (el.checked) {
                check = 1;
            }
            else {

                check = 0;
            }
            fetch('/user/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    dark_mode: check
                })
            });
            const body = document.getElementsByTagName('body')[0];
            const hr = document.querySelectorAll('div:not(.sidenav) > hr');
            const hr_card = document.querySelectorAll('div:not(.bg-gradient-dark) hr');
            const text_btn = document.querySelectorAll('button:not(.btn) > .text-dark');
            const text_span = document.querySelectorAll('span.text-dark, .breadcrumb .text-dark');
            const text_span_white = document.querySelectorAll('span.text-white, .breadcrumb .text-white');
            const text_strong = document.querySelectorAll('strong.text-dark');
            const text_strong_white = document.querySelectorAll('strong.text-white');
            const text_nav_link = document.querySelectorAll('a.nav-link.text-dark');
            const text_nav_link_white = document.querySelectorAll('a.nav-link.text-white');
            const secondary = document.querySelectorAll('.text-secondary');
            const bg_gray_100 = document.querySelectorAll('.bg-gray-100');
            const bg_gray_600 = document.querySelectorAll('.bg-gray-600');
            const btn_text_dark = document.querySelectorAll('.btn.btn-link.text-dark, .material-symbols-rounded.text-dark');
            const btn_text_white = document.querySelectorAll('.btn.btn-link.text-white, .material-symbols-rounded.text-white');
            const card_border = document.querySelectorAll('.card.border');
            const card_border_dark = document.querySelectorAll('.card.border.border-dark');

            const svg = document.querySelectorAll('g');

            if (!el.getAttribute("checked")) {
                body.classList.add('dark-version');
                for (var i = 0; i < hr.length; i++) {
                    if (hr[i].classList.contains('dark')) {
                        hr[i].classList.remove('dark');
                        hr[i].classList.add('light');
                    }
                }

                for (var i = 0; i < hr_card.length; i++) {
                    if (hr_card[i].classList.contains('dark')) {
                        hr_card[i].classList.remove('dark');
                        hr_card[i].classList.add('light');
                    }
                }
                for (var i = 0; i < text_btn.length; i++) {
                    if (text_btn[i].classList.contains('text-dark')) {
                        text_btn[i].classList.remove('text-dark');
                        text_btn[i].classList.add('text-white');
                    }
                }
                for (var i = 0; i < text_span.length; i++) {
                    if (text_span[i].classList.contains('text-dark')) {
                        text_span[i].classList.remove('text-dark');
                        text_span[i].classList.add('text-white');
                    }
                }
                for (var i = 0; i < text_strong.length; i++) {
                    if (text_strong[i].classList.contains('text-dark')) {
                        text_strong[i].classList.remove('text-dark');
                        text_strong[i].classList.add('text-white');
                    }
                }
                for (var i = 0; i < text_nav_link.length; i++) {
                    if (text_nav_link[i].classList.contains('text-dark')) {
                        text_nav_link[i].classList.remove('text-dark');
                        text_nav_link[i].classList.add('text-white');
                    }
                }
                for (var i = 0; i < secondary.length; i++) {
                    if (secondary[i].classList.contains('text-secondary')) {
                        secondary[i].classList.remove('text-secondary');
                        secondary[i].classList.add('text-white');
                        secondary[i].classList.add('opacity-8');
                    }
                }
                for (var i = 0; i < bg_gray_100.length; i++) {
                    if (bg_gray_100[i].classList.contains('bg-gray-100')) {
                        bg_gray_100[i].classList.remove('bg-gray-100');
                        bg_gray_100[i].classList.add('bg-gray-600');
                    }
                }
                for (var i = 0; i < btn_text_dark.length; i++) {
                    btn_text_dark[i].classList.remove('text-dark');
                    btn_text_dark[i].classList.add('text-white');
                }
                for (var i = 0; i < svg.length; i++) {
                    if (svg[i].hasAttribute('fill')) {
                        svg[i].setAttribute('fill', '#fff');
                    }
                }
                for (var i = 0; i < card_border.length; i++) {
                    card_border[i].classList.add('border-dark');
                }
                el.setAttribute("checked", "true");
            } else {
                body.classList.remove('dark-version');
                for (var i = 0; i < hr.length; i++) {
                    if (hr[i].classList.contains('light')) {
                        hr[i].classList.add('dark');
                        hr[i].classList.remove('light');
                    }
                }
                for (var i = 0; i < hr_card.length; i++) {
                    if (hr_card[i].classList.contains('light')) {
                        hr_card[i].classList.add('dark');
                        hr_card[i].classList.remove('light');
                    }
                }
                for (var i = 0; i < text_btn.length; i++) {
                    if (text_btn[i].classList.contains('text-white')) {
                        text_btn[i].classList.remove('text-white');
                        text_btn[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < text_span_white.length; i++) {
                    if (text_span_white[i].classList.contains('text-white') && !text_span_white[i].closest('.sidenav') && !text_span_white[i].closest('.card.bg-gradient-dark')) {
                        text_span_white[i].classList.remove('text-white');
                        text_span_white[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < text_strong_white.length; i++) {
                    if (text_strong_white[i].classList.contains('text-white')) {
                        text_strong_white[i].classList.remove('text-white');
                        text_strong_white[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < text_nav_link_white.length; i++) {
                    if (text_nav_link_white[i].classList.contains('text-white') && !text_nav_link_white[i].closest('.sidenav')) {
                        text_nav_link_white[i].classList.remove('text-white');
                        text_nav_link_white[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < secondary.length; i++) {
                    if (secondary[i].classList.contains('text-white')) {
                        secondary[i].classList.remove('text-white');
                        secondary[i].classList.remove('opacity-8');
                        secondary[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < bg_gray_600.length; i++) {
                    if (bg_gray_600[i].classList.contains('bg-gray-600')) {
                        bg_gray_600[i].classList.remove('bg-gray-600');
                        bg_gray_600[i].classList.add('bg-gray-100');
                    }
                }
                for (var i = 0; i < svg.length; i++) {
                    if (svg[i].hasAttribute('fill')) {
                        svg[i].setAttribute('fill', '#252f40');
                    }
                }
                for (var i = 0; i < btn_text_white.length; i++) {
                    if (!btn_text_white[i].closest('.card.bg-gradient-dark')) {
                        btn_text_white[i].classList.remove('text-white');
                        btn_text_white[i].classList.add('text-dark');
                    }
                }
                for (var i = 0; i < card_border_dark.length; i++) {
                    card_border_dark[i].classList.remove('border-dark');
                }
                el.removeAttribute("checked");


            }
        };

    </script>


 
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>
    <!--   Core JS Files   -->
  <script src="{{ asset('argon/js/core/bootstrap.bundle.min.js') }}"></script>
    <script src="{{asset('argon/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('argon/js/plugins/smooth-scrollbar.min.js')}}"></script>
    <script src="{{asset('argon/js/plugins/chartjs.min.js')}}"></script>



    
<style>
    .alertify .ajs-modal {
    display: flex !important;
    justify-content: center;
    align-items: center;
}

.alertify .ajs-dialog {
    margin: 0 auto !important;

    transform: translateY(-40%) !important;
}
</style>
    <script>

        alertify.defaults.theme.ok = "btn btn-danger";  
        alertify.defaults.theme.cancel = "btn btn-secondary";
        alertify.defaults.theme.input = "form-control";  
        alertify.defaults.glossary.title = "Confirmar acción"; 
        alertify.defaults.transition = "zoom";             
      
        
        function confirmarEliminacion(formId, mensaje = '¿Estás seguro de que deseas eliminar este elemento?') {
            alertify.confirm(
                'Confirmar eliminación',
                mensaje,
                function () {
                    document.getElementById(formId).submit();
                },
                function () {
                    alertify.error('Eliminación cancelada');
                }
            ).set('labels', { ok: 'Eliminar', cancel: 'Cancelar' });
        }

        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

       
    </script>

  
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    <script src="{{asset('argon/js/argon-dashboard.js?v=2.1.0')}}"></script>

</body>

</html>