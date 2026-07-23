<style>
    .notification-wrapper {
        position: relative;
    }

    #notificationBox {
        position: fixed !important;
        top: 44px;
        right: 42px;

        width: 300px;
        max-height: 360px;
        overflow-y: auto;

        z-index: 999999;
        display: none;

        font-size: 12px;
    }

    #notificationBox.active {
        display: block;
    }

    #notificationBox ul {
        padding: 0;
        margin: 0;
    }

    .notification-item {
        padding: 8px 10px !important;
        line-height: 1.2;
    }

    .notification-item strong {
        font-size: 11px;
    }

    .notification-item .flex-grow-1 {
        font-size: 13px;
    }

    .notification-item .btn-group {
        gap: 3px;
    }

    .notification-item .btn {
        width: 25px;
        height: 25px;
        padding: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-item .btn i {
        font-size: 11px;
    }

    #notificationFooter {
        padding: 6px !important;
    }

    #notificationFooter button {
        font-size: 12px;
        padding: 5px;
    }

    #notificationBox::-webkit-scrollbar {
        width: 5px;
    }

    #notificationBox::-webkit-scrollbar-thumb {
        border-radius: 10px;
    }
</style>
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur"
    data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <div class="row w-100 g-0">
            <div class="col-12 col-md-auto order-2 order-md-1">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        @foreach ($breadcrumb as $key => $crumb)
                            @if ($key == count($breadcrumb) - 1)
                                <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                                    {{ $crumb['name'] }}</li>
                            @else
                                <li class="breadcrumb-item text-sm">
                                    <a class="opacity-5 text-white" href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                                </li>
                            @endif
                        @endforeach
                    </ol>

                </nav>
            </div>
            <div class="col-12 col-md order-1 order-md-2 d-flex justify-content-end align-items-center">
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">

                    </div>
                    <ul class="navbar-nav justify-content-end align-items-center">

                        <li class="nav-item d-xl-none d-flex align-items-center me-3">
                            <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                    <i class="sidenav-toggler-line bg-white"></i>
                                </div>
                            </a>
                        </li>

                        {{-- Configuración --}}
                        <li class="nav-item d-flex align-items-center me-3">
                            <a href="javascript:;" class="nav-link text-white p-0">
                                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer" style=""></i>
                            </a>
                        </li>
                        <style>
                            .notification-item {
                                transition: all .35s ease;
                            }

                            .notification-item.fade-out {
                                opacity: 0;
                                transform: translateX(25px);
                            }
                        </style>
                        {{-- Notificaciones --}}
                        <li class="nav-item d-flex align-items-center">

                            @php
                                $tieneNotificaciones = false;
                                $cantidadNotificaciones = 0;
                                $notificaciones = collect();

                                if (Auth::check()) {
                                    $cantidadNotificaciones = Auth::user()->unreadNotifications->count();
                                    $tieneNotificaciones = $cantidadNotificaciones > 0;
                                    $notificaciones = Auth::user()->unreadNotifications;
                                }
                            @endphp

                            <div class="notification-wrapper position-relative">

                                <a href="javascript:;"
                                    class="nav-link text-white p-0 notification-icon {{ $tieneNotificaciones ? 'has-notifications' : '' }}"
                                    id="notificationTrigger">

                                    <i class="fas fa-bell text-white cursor-pointer" style=""></i>
                                    @if ($tieneNotificaciones)
                                        <span class="badge" id="notificationCount">
                                            {{ $cantidadNotificaciones }}
                                        </span>
                                    @endif
                                </a>

                                <div id="notificationBox"
                                    class="notification-box {{ isset($preferencias) && $preferencias->dark_mode ? 'dark-version' : '' }}">

                                    <ul id="notificationList">

                                        @forelse($notificaciones as $notification)
                                            @php
                                                $data = $notification->data ?? [];
                                                $message = $data['message'] ?? 'Sin mensaje';
                                                $actionUrl = $data['url'] ?? '#';
                                            @endphp

                                            <li class="list-group-item notification-item"
                                                id="notification-{{ $notification->id }}">

                                                <div class="d-flex justify-content-between align-items-start">

                                                    <div class="flex-grow-1 me-2">

                                                        <strong>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </strong>

                                                        <br>

                                                        {{ $message }}

                                                    </div>

                                                    <div class="btn-group btn-group-xs">

                                                        <a href="{{ $actionUrl }}"
                                                            class="btn btn-xs btn-outline-primary" title="Ver">

                                                            <i class="fas fa-eye"></i>

                                                        </a>

                                                        <button class="btn btn-xs btn-outline-success"
                                                            title="Marcar como leída"
                                                            onclick="MarcarNotificacionLeida('{{ $notification->id }}', this)">

                                                            <i class="fas fa-check"></i>

                                                        </button>

                                                    </div>

                                                </div>

                                            </li>

                                        @empty

                                            <li class="list-group-item text-center">
                                                No hay notificaciones nuevas
                                            </li>
                                        @endforelse

                                    </ul>
                                    @if ($tieneNotificaciones)
                                        <div class="p-2 border-top" id="notificationFooter">

                                            <button class="btn btn-primary btn-sm w-100" onclick="MarcarTodasLeidas()">

                                                <i class="fas fa-check-double me-1"></i>

                                                Marcar todas como leídas

                                            </button>

                                        </div>
                                    @endif
                                </div>

                            </div>

                        </li>

                    </ul>

                </div>

            </div>


        </div>
    </div>

</nav>
<!-- End Navbar -->
