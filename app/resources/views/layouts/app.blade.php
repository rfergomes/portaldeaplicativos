<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal de Aplicativos') | AdminLTE 4</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css">
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    @stack('styles')

    <script>
        // Color Mode Toggler
        (() => {
            "use strict";

            const storedTheme = localStorage.getItem("theme");

            const getPreferredTheme = () => {
                if (storedTheme) {
                    return storedTheme;
                }
                return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            };

            const setTheme = function (theme) {
                if (theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    document.documentElement.setAttribute("data-bs-theme", "dark");
                } else {
                    document.documentElement.setAttribute("data-bs-theme", theme);
                }
            };

            setTheme(getPreferredTheme());

            const showActiveTheme = (theme, focus = false) => {
                const themeSwitcher = document.querySelector("#bd-theme");

                if (!themeSwitcher) {
                    return;
                }

                const themeSwitcherText = document.querySelector("#bd-theme-text");
                const activeThemeIcon = document.querySelector(".theme-icon-active i");
                const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);
                const svgOfActiveBtn = btnToActive.querySelector("i").getAttribute("class");

                for (const element of document.querySelectorAll("[data-bs-theme-value]")) {
                    element.classList.remove("active");
                    element.setAttribute("aria-pressed", "false");
                }

                btnToActive.classList.add("active");
                btnToActive.setAttribute("aria-pressed", "true");
                activeThemeIcon.setAttribute("class", svgOfActiveBtn);
                const themeSwitcherLabel = `${themeSwitcherText.textContent} (${btnToActive.dataset.bsThemeValue})`;
                themeSwitcher.setAttribute("aria-label", themeSwitcherLabel);

                if (focus) {
                    themeSwitcher.focus();
                }
            };

            window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
                const storedTheme = localStorage.getItem("theme");
                if (storedTheme !== "light" && storedTheme !== "dark") {
                    setTheme(getPreferredTheme());
                }
            });

            window.addEventListener("DOMContentLoaded", () => {
                showActiveTheme(getPreferredTheme());

                for (const toggle of document.querySelectorAll("[data-bs-theme-value]")) {
                    toggle.addEventListener("click", () => {
                        const theme = toggle.getAttribute("data-bs-theme-value");
                        localStorage.setItem("theme", theme);
                        setTheme(theme);
                        showActiveTheme(theme, true);
                    });
                }
            });
        })();
    </script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Navbar -->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <!-- Start navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fa-solid fa-bars"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
                    </li>
                </ul>
                <!-- End navbar links -->

                <ul class="navbar-nav ms-auto">
                    <!-- Color Mode Toggler -->
                    <li class="nav-item dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center" id="bd-theme"
                            type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static">
                            <span class="theme-icon-active">
                                <i class="fa-solid fa-moon my-1"></i>
                            </span>
                            <span class="d-lg-none ms-2" id="bd-theme-text">Toggle theme</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text"
                            style="--bs-dropdown-min-width: 8rem;">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center"
                                    data-bs-theme-value="light" aria-pressed="false">
                                    <i class="fa-solid fa-sun me-2"></i>
                                    Light
                                    <i class="fa-solid fa-check ms-auto d-none"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center"
                                    data-bs-theme-value="dark" aria-pressed="false">
                                    <i class="fa-solid fa-moon me-2"></i>
                                    Dark
                                    <i class="fa-solid fa-check ms-auto d-none"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center"
                                    data-bs-theme-value="auto" aria-pressed="true">
                                    <i class="fa-solid fa-circle-half-stroke me-2"></i>
                                    Auto
                                    <i class="fa-solid fa-check ms-auto d-none"></i>
                                </button>
                            </li>
                        </ul>
                    </li>

                    <!-- User Menu -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header bg-primary">
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>Membro desde {{ Auth::user()->created_at->format('M. Y') }}</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-default btn-flat float-end">Sair</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="{{ route('dashboard') }}" class="brand-link">
                    <span class="brand-text fw-light">Portal de Aplicativos</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2 text-white">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-gauge-high"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if(auth()->user()->temPermissao('ver_eventos'))
                            <li class="nav-item">
                                <a href="{{ route('eventos.index') }}"
                                    class="nav-link {{ request()->routeIs('eventos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-calendar-days"></i>
                                    <p>Eventos</p>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->temPermissao('ver_protocolos'))
                            <li class="nav-item">
                                <a href="{{ route('protocolos.index') }}"
                                    class="nav-link {{ request()->routeIs('protocolos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-file-signature"></i>
                                    <p>Protocolos</p>
                                </a>
                            </li>
                        @endif

                        <li class="nav-header">CADASTRO</li>
                        <li class="nav-item">
                            <a href="{{ route('empresas.index') }}"
                                class="nav-link {{ request()->routeIs('empresas.*') || request()->routeIs('clientes.*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-building"></i>
                                <p>Empresas / Contatos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('tipos_clientes.index') }}"
                                class="nav-link {{ request()->routeIs('tipos_clientes.*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-id-badge"></i>
                                <p>Tipos de Contatos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('regioes.index') }}"
                                class="nav-link {{ request()->routeIs('regioes.*') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-map-location-dot"></i>
                                <p>Regiões</p>
                            </a>
                        </li>

                        @if(auth()->user()->temPermissao('administrar_usuarios'))
                            <li class="nav-header">ADMINISTRAÇÃO</li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-users-gear"></i>
                                    <p>Usuários & Perfis</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('protocolos.tipos.index') }}"
                                    class="nav-link {{ request()->routeIs('protocolos.tipos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-tags"></i>
                                    <p>Tipos de Protocolo</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('token-deptos.index') }}"
                                    class="nav-link {{ request()->routeIs('token-deptos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-key"></i>
                                    <p>Tokens AR-Online</p>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('title')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="app-footer text-center">
            <strong>Copyright &copy; 2024-2026 &nbsp;<a href="#" class="text-decoration-none">TI Químicos
                    Unificados</a>.</strong> All rights reserved.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <!-- OverlayScrollbars -->
    <script
        src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <!-- Popperjs for Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <!-- AdminLTE 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function () {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });

        // Configuração global do SweetAlert2 Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Disparar notificações de sessão
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: "{{ session('warning') }}"
            });
        @endif

        // Inicializar Select2 globalmente
        $(document).ready(function () {
            $('.form-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                language: {
                    noResults: function () {
                        return "Nenhum resultado encontrado";
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>