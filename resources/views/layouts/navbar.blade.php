 <!-- Navbar -->
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
                                                    @php
                                                        $data = $notification->data ?? [];
                                                        $message = $data['message'] ?? 'Sin mensaje';
                                                        $actionUrl = $data['url'] ?? '#'; 
                                                    @endphp
                                                    <li class="list-group-item">
                                                        <a href="{{ $actionUrl }}"
                                                            onclick="NotificacionLeida(event,'{{ $notification->id }}')"
                                                            style="text-decoration: none;" class="text-black float-right">
                                                            <strong>{{ $notification->created_at->diffForHumans() }}</strong>
                                                            - {{ $message }}
                                                        </a>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item">No hay notificaciones nuevas</li>
                                                @endforelse

                                            </ul>
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