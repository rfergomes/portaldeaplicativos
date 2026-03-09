<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <style>
        .user-avatar-circle {
            width: 40px;
            height: 40px;
            background-color: #033c5a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        [data-bs-theme="dark"] .user-avatar-circle {
            background-color: #fff;
            color: #033c5a;
        }

        .user-menu:hover .user-avatar-circle {
            transform: scale(1.05);
        }

        .header-info-left {
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding-right: 15px;
            margin-right: 15px;
            text-align: left;
            line-height: 1.2;
        }

        .header-info-left .user-profile-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            display: block;
        }

        [data-bs-theme="dark"] .header-info-left .user-profile-label {
            color: #94a3b8;
        }

        .header-info-left .user-nick {
            font-size: 0.75rem;
            background: #f1f5f9;
            padding: 1px 12px;
            border-radius: 50rem;
            color: #334155;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            margin-bottom: 2px;
        }

        [data-bs-theme="dark"] .header-info-left .user-nick {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        .header-info-left .user-nick::before {
            content: "";
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 8px;
        }

        .app-header.navbar {
            background-color: #fff !important;
            color: #334155 !important;
        }

        [data-bs-theme="dark"] .app-header.navbar {
            background-color: var(--bs-body-bg) !important;
            color: #fff !important;
        }

        .app-header .nav-link {
            color: #4b5563 !important;
        }

        [data-bs-theme="dark"] .app-header .nav-link {
            color: #fff !important;
        }

        .dropdown-user-premium {
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 0;
            min-width: 260px;
            overflow: hidden;
        }

        .dropdown-user-premium .user-header-new {
            padding: 15px 20px;
            background: #fff;
            border-bottom: 1px solid #f1f5f9;
        }

        [data-bs-theme="dark"] .dropdown-user-premium .user-header-new {
            background: var(--bs-secondary-bg);
            border-bottom-color: rgba(255, 255, 255, 0.05);
        }

        .dropdown-user-premium .user-header-new .user-avatar-lg {
            width: 50px;
            height: 50px;
            background-color: #033c5a;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1.4rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        [data-bs-theme="dark"] .dropdown-user-premium .user-header-new .user-avatar-lg {
            background-color: #f8fafc;
            color: #033c5a;
        }

        .dropdown-user-premium .user-header-new .dropdown-item-title {
            font-size: 1rem;
            margin: 0;
            font-weight: 700;
            color: #1e293b;
        }

        [data-bs-theme="dark"] .dropdown-user-premium .user-header-new .dropdown-item-title {
            color: #f8fafc;
        }

        .dropdown-user-premium .user-header-new .user-role-badge {
            font-size: 0.7rem;
            color: #033c5a;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1px;
            display: block;
        }

        [data-bs-theme="dark"] .dropdown-user-premium .user-header-new .user-role-badge {
            color: #cbd5e1;
            opacity: 0.8;
        }

        .dropdown-user-premium .user-header-new .user-email-text {
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 4px;
            word-break: break-all;
        }

        [data-bs-theme="dark"] .dropdown-user-premium .user-header-new .user-email-text {
            color: #94a3b8;
        }

        .dropdown-user-premium .user-meta {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0;
            line-height: 1.3;
        }

        .dropdown-user-premium .user-meta i {
            width: auto;
            font-size: 0.7rem;
            margin-right: 4px;
        }

        .dropdown-user-premium .dropdown-item {
            padding: 12px 20px;
            font-weight: 500;
            color: #4b5563;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .dropdown-user-premium .dropdown-item:hover {
            background-color: #f8fafc;
            color: #033c5a;
        }

        .dropdown-user-premium .dropdown-item i {
            width: 25px;
            font-size: 1.1rem;
            opacity: 0.6;
        }

        .dropdown-user-premium .divider-light {
            height: 1px;
            background-color: #f1f5f9;
            margin: 0;
        }

        /* Estilo Premium para Tabelas */
        .table.premium-table thead {
            background-color: #ffffff !important;
            color: #334155 !important;
            border-bottom: 2px solid #e2e8f0;
        }

        .table.premium-table thead th {
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        [data-bs-theme="dark"] .table.premium-table thead {
            background-color: #ffffff !important;
            color: #1e293b !important;
        }
    </style>

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
                if (!themeSwitcher) return;

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
                    <!-- Informações de Localização e Usuário (Esquerda do sino) -->
                    <li class="nav-item d-none d-sm-flex align-items-center">
                        <div class="header-info-left">
                            <div class="user-profile-label">{{ Auth::user()->perfis->first()->nome ?? 'USUÁRIO' }}</div>
                            <div class="user-nick">{{ Auth::user()->nickname ?: Auth::user()->name }}</div>
                        </div>
                    </li>

                    <!-- Notificações -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-regular fa-bell"></i>
                        </a>
                    </li>

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
                    <li class="nav-item dropdown user-menu ms-2">
                        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center p-0"
                            data-bs-toggle="dropdown">
                            <div class="user-avatar-circle">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-user-premium shadow">
                            <li class="user-header-new">
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-lg">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 class="dropdown-item-title">
                                            {{ Auth::user()->name }}
                                        </h3>
                                        <div class="user-role-badge">
                                            {{ Auth::user()->perfis->first()->nome ?? 'USUÁRIO' }}
                                        </div>
                                        <div class="user-email-text">{{ Auth::user()->email }}</div>
                                        <div class="user-meta">
                                            <div>
                                                <i class="fa-regular fa-calendar-days"></i>
                                                Membro desde {{ Auth::user()->created_at->format('M. Y') }}
                                            </div>
                                            @if(Auth::user()->last_login_at)
                                                <div>
                                                    <i class="fa-regular fa-clock"></i>
                                                    Acesso: {{ Auth::user()->last_login_at->format('d/m H:i') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <a href="{{ route('profile.index') }}" class="dropdown-item">
                                    <i class="fa-regular fa-user me-2"></i> Meu Perfil
                                </a>
                            </li>
                            <li>
                                <div class="divider-light"></div>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-header-form">
                                    @csrf
                                    <a href="#" class="dropdown-item text-danger" style="color: #ef4444 !important;"
                                        onclick="event.preventDefault(); document.getElementById('logout-header-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket me-2"></i> Sair
                                    </a>
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

                        @if(auth()->user()->temPermissao('eventos.visualizar'))
                            <li class="nav-item">
                                <a href="{{ route('eventos.index') }}"
                                    class="nav-link {{ request()->routeIs('eventos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-calendar-days"></i>
                                    <p>Eventos</p>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->temPermissao('protocolos.visualizar'))
                            <li class="nav-item">
                                <a href="{{ route('protocolos.index') }}"
                                    class="nav-link {{ request()->routeIs('protocolos.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-file-signature"></i>
                                    <p>Protocolos</p>
                                </a>
                            </li>
                        @endif

                        <!-- Agenda Colônia -->
                        @if(auth()->user()->temPermissao('reservas.visualizar') || auth()->user()->temPermissao('colonias.visualizar'))
                            <li class="nav-item {{ request()->routeIs('agenda.*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->routeIs('agenda.*') ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-hotel"></i>
                                    <p>
                                        Agenda Colônia
                                        <i class="nav-arrow fa-solid fa-chevron-right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if(auth()->user()->temPermissao('reservas.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('agenda.reservas.index') }}"
                                                class="nav-link {{ request()->routeIs('agenda.reservas.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-calendar-check"></i>
                                                <p>Painel de Reservas</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('periodos.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('agenda.periodos.index') }}"
                                                class="nav-link {{ request()->routeIs('agenda.periodos.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-clock"></i>
                                                <p>Períodos</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('colonias.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('agenda.colonias.index') }}"
                                                class="nav-link {{ request()->routeIs('agenda.colonias.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-umbrella-beach"></i>
                                                <p>Cadastrar Colônias</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('inscricoes.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('agenda.inscricoes.index') }}"
                                                class="nav-link {{ request()->routeIs('agenda.inscricoes.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-ticket"></i>
                                                <p>Inscrições / Sorteio</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('hospedes.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('agenda.historico.index') }}"
                                                class="nav-link {{ request()->routeIs('agenda.historico.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-trash-can text-danger"></i>
                                                <p>Exclusões</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <!-- Cadastro -->
                        @if(auth()->user()->temPermissao('empresas.visualizar') || auth()->user()->temPermissao('regioes.visualizar'))
                            @php
                                $isCadastroActive = request()->routeIs('empresas.*') || request()->routeIs('clientes.*') || request()->routeIs('tipos_clientes.*') || request()->routeIs('regioes.*');
                            @endphp
                            <li class="nav-item {{ $isCadastroActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $isCadastroActive ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-folder-plus"></i>
                                    <p>
                                        Cadastro
                                        <i class="nav-arrow fa-solid fa-chevron-right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if(auth()->user()->temPermissao('empresas.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('empresas.index') }}"
                                                class="nav-link {{ request()->routeIs('empresas.*') || request()->routeIs('clientes.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-building"></i>
                                                <p>Empresas / Contatos</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('tipos_clientes.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('tipos_clientes.index') }}"
                                                class="nav-link {{ request()->routeIs('tipos_clientes.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-id-badge"></i>
                                                <p>Tipos de Contatos</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('regioes.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('regioes.index') }}"
                                                class="nav-link {{ request()->routeIs('regioes.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-map-location-dot"></i>
                                                <p>Regiões</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        <!-- Administração -->
                        @if(auth()->user()->temPermissao('usuarios.visualizar') || auth()->user()->temPermissao('administrar_usuarios'))
                            @php
                                $isAdminActive = request()->routeIs('users.*') || request()->routeIs('perfis.*') || request()->routeIs('protocolos.tipos.*') || request()->routeIs('token-deptos.*');
                            @endphp
                            <li class="nav-item {{ $isAdminActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $isAdminActive ? 'active' : '' }}">
                                    <i class="nav-icon fa-solid fa-gears"></i>
                                    <p>
                                        Administração
                                        <i class="nav-arrow fa-solid fa-chevron-right"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if(auth()->user()->temPermissao('usuarios.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('users.index') }}"
                                                class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-users"></i>
                                                <p>Usuários</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('perfis.index') }}"
                                                class="nav-link {{ request()->routeIs('perfis.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-user-lock"></i>
                                                <p>Perfis e Acessos</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('protocolos_tipos.visualizar'))
                                        <li class="nav-item">
                                            <a href="{{ route('protocolos.tipos.index') }}"
                                                class="nav-link {{ request()->routeIs('protocolos.tipos.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-tags"></i>
                                                <p>Tipos de Protocolo</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if(auth()->user()->temPermissao('administrar_usuarios'))
                                        <li class="nav-item">
                                            <a href="{{ route('token-deptos.index') }}"
                                                class="nav-link {{ request()->routeIs('token-deptos.*') ? 'active' : '' }}">
                                                <i class="nav-icon fa-solid fa-key"></i>
                                                <p>Tokens AR-Online</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        <li class="nav-header">CONTA</li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-danger"
                                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                <i class="nav-icon fa-solid fa-right-from-bracket"></i>
                                <p>Sair do Sistema</p>
                            </a>
                            <form id="logout-form-sidebar" method="POST" action="{{ route('logout') }}"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
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
                            <h3 class="mb-0 text-capitalize">@yield('title')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                @php $link = ""; @endphp
                                @foreach(Request::segments() as $segment)
                                    @if(!is_numeric($segment) && !in_array($segment, ['public', 'index']))
                                        @php $link .= "/" . $segment; @endphp
                                        <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }} text-capitalize" 
                                            {!! $loop->last ? 'aria-current="page"' : '' !!}>
                                            @if($loop->last)
                                                {{ str_replace(['-', '_'], ' ', $segment) }}
                                            @else
                                                <a href="{{ url($link) }}">{{ str_replace(['-', '_'], ' ', $segment) }}</a>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
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
            <strong>Copyright &copy; 2024-{{ date('Y') }} &nbsp;<a href="#" class="text-decoration-none">Portal de
                    Aplicativos - TI
                    Químicos
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
            $('.form-select').each(function () {
                const $this = $(this);
                const isInsideModal = $this.closest('.modal').length > 0;

                $this.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: isInsideModal ? $this.closest('.modal') : $(document.body),
                    language: {
                        noResults: function () {
                            return "Nenhum resultado encontrado";
                        }
                    }
                });
            });
        });

        // Função global para confirmação de exclusão com SweetAlert2
        function confirmDelete(formId, message = 'Tem certeza que deseja excluir este registro?') {
            Swal.fire({
                title: 'Confirmação',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>