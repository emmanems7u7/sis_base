<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('monito.png') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('argon/css/argon-dashboard.css?v=2.1.0') }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />


    @vite(['resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>



    <!-- CodeMirror 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/dracula.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/htmlmixed/htmlmixed.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>

</head>

@if ($isMobile)
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light.css">
@endif
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

    /* Opcional: centrado con flex */
    #overlay-spinner {
        display: flex;
    }

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

<div id="overlay-spinner"
    style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.5);
    z-index:9999;
    justify-content:center;
    align-items:center;
    flex-direction: column;
    color: white;
">
    <div class="spinner-border text-light mb-2" role="status"></div>
    <span>Cargando...</span>
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
                <img src="{{ asset('monito.png') }}" style="" alt="">
                <span class="ms-1 font-weight-bold">{{ config('app.name', 'Laravel') }}</span>
            </a>
        </div>

        <div class="collapse  text-black navbar-collapse w-auto" id="sidenav-collapse-main">
            @include('layouts.lateral')
        </div>
        <!-- CDN de SortableJS -->
        @if ($configuracion->mantenimiento == 1)
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
            <script>
                const lista = document.getElementById('secciones-list');

                new Sortable(lista, {
                    animation: 150,
                    onEnd: function() {
                        const orden = Array.from(document.querySelectorAll('.seccion-item'))
                            .map((el, index) => ({
                                id: el.dataset.id,
                                posicion: index + 1
                            }));

                        fetch('{{ route('secciones.ordenar') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    orden
                                })
                            }).then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    mostrarAlerta('success', data.message);
                                } else {
                                    mostrarAlerta('error', data.message || 'Ocurrió un error al ordenar');

                                }
                            })
                    }
                });
            </script>
        @endif

    </aside>

    <main class="main-content position-relative border-radius-lg ">

        @include('layouts.navbar')


        <div class="container-fluid">


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    alertify.set('notifier', 'position', 'top-right');

                    @foreach (['status' => 'success', 'error' => 'error', 'warning' => 'warning'] as $msg => $type)
                        @if (session($msg))
                            alertify.{{ $type }}(@json(session($msg)));
                        @endif
                    @endforeach

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            alertify.error(@json($error));
                        @endforeach
                    @endif
                });
            </script>

            @yield('content')

        </div>


    </main>


    @include('layouts.personalizacion')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('overlay-spinner');

            document.addEventListener('submit', function(e) {

                const offcanvasEl = document.getElementById('offcanvasAcciones');

                if (offcanvasEl) {
                    const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    offcanvas.hide();
                }

                if (overlay) {
                    overlay.style.display = 'flex';
                }
            });
        });

        Fancybox.bind("[data-fancybox='gallery']", {
            Toolbar: {
                display: [
                    "zoom",
                    "close"
                ]
            }
        });

        (function() {
            const overlay = document.getElementById('overlay-spinner');
            const originalFetch = window.fetch;
            let activeFetches = 0;

            window.fetch = async function(input, options = {}) {

                const showOverlay = options?.showOverlay !== false;

                if (showOverlay) {
                    activeFetches++;
                    overlay.style.display = 'flex';
                }

                try {
                    const response = await originalFetch(input, options);
                    return response;
                } catch (err) {
                    console.error(err);
                    throw err;
                } finally {

                    if (showOverlay) {
                        activeFetches--;
                        if (activeFetches <= 0) {
                            overlay.style.display = 'none';
                            activeFetches = 0;
                        }
                    }
                }
            };
        })();

        function sidebarColor(a) {

            var parent = document.querySelector(".nav-link.active");
            var color = a.getAttribute("data-color");

            // Limpiar clases anteriores
            [
                'primary', 'dark', 'info', 'success', 'warning', 'danger'
            ].forEach(function(c) {
                parent.classList.remove('bg-gradient-' + c);
            });

            // Agregar nuevo color
            parent.classList.add('bg-gradient-' + color);

            // Marcar badge activo
            document.querySelectorAll('.badge.filter').forEach(function(el) {
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
                    body: JSON.stringify({
                        color: color
                    })
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
                body: JSON.stringify({
                    sidebar_type: selectedType
                }),
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
                    i = [], r = 0; r < t.length; r++) t[r].classList.remove("active"), i.push(t[r].getAttribute(
                "data-class"));
            e.classList.contains("active") ? e.classList.remove("active") : e.classList.add("active");
            for (var l, o, c, d = document.querySelector(".sidenav"), r = 0; r < i.length; r++) d.classList.remove(i[r]);
            if (d.classList.add(s), "bg-transparent" == s || "bg-white" == s) {
                var u = document.querySelectorAll(".sidenav .text-white:not(.nav-link-text):not(.active)");
                for (let e = 0; e < u.length; e++) u[e].classList.remove("text-white"), u[e].classList.add("text-dark")
            } else {
                var f = document.querySelectorAll(".sidenav .text-dark");
                for (let e = 0; e < f.length; e++) f[e].classList.add("text-white"), f[e].classList.remove("text-dark")
            }
            if ("bg-transparent" == s && n) {
                f = document.querySelectorAll(".navbar-brand .text-dark");
                for (let e = 0; e < f.length; e++) f[e].classList.add("text-white"), f[e].classList.remove("text-dark")
            }
            "bg-transparent" != s && "bg-white" != s || !a ? (o = (l = document.querySelector(".navbar-brand-img")).src)
                .includes("logo-ct-dark.png") && (c = o.replace("logo-ct-dark", "logo-ct"), l.src = c) : (o = (l = document
                    .querySelector(".navbar-brand-img")).src).includes("logo-ct.png") && (c = o.replace("logo-ct",
                    "logo-ct-dark"), l.src = c), "bg-white" == s && n && (o = (l = document.querySelector(
                    ".navbar-brand-img")).src).includes("logo-ct.png") && (c = o.replace("logo-ct", "logo-ct-dark"), l.src =
                    c)




        }
    </script>


    <script>
        function darkMode(el) {
            var check;
            if (el.checked) {
                check = 1;
            } else {

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
            const btn_text_white = document.querySelectorAll(
                '.btn.btn-link.text-white, .material-symbols-rounded.text-white');
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
                    if (text_span_white[i].classList.contains('text-white') && !text_span_white[i].closest('.sidenav') && !
                        text_span_white[i].closest('.card.bg-gradient-dark')) {
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
                    if (text_nav_link_white[i].classList.contains('text-white') && !text_nav_link_white[i].closest(
                            '.sidenav')) {
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
    <script src="{{ asset('argon/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ asset('js/alertas.js') }}"></script>
    <script src="{{ asset('js/export.js') }}"></script>

    <script src="{{ asset('js/campos.js') }}"></script>
    <script src="{{ asset('js/iconos.js') }}"></script>
    <script src="{{ asset('js/offcanvas.js') }}"></script>



    <script>
        alertify.defaults.theme.ok = "btn btn-danger";
        alertify.defaults.theme.cancel = "btn btn-secondary";
        alertify.defaults.theme.input = "form-control";
        alertify.defaults.glossary.title = "Confirmar acción";
        alertify.defaults.transition = "zoom";

        function confirmarEliminacion(
            formId,
            mensaje = '¿Estás seguro de que deseas eliminar este elemento?',
            callback = null
        ) {
            alertify.confirm(
                'Confirmar acción',
                mensaje,
                function() {

                    if (callback) {
                        callback();
                        return;
                    }

                    document.getElementById(formId).submit();
                },
                function() {
                    alertify.error('Acción cancelada');
                }
            ).set('labels', {
                ok: 'Aceptar',
                cancel: 'Cancelar'
            });
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

    <script src="{{ asset('argon/js/argon-dashboard.js?v=2.1.0') }}"></script>

</body>

</html>
