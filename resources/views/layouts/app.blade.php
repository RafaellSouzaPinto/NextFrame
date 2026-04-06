<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NextFrame')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    {{-- Bootstrap: usado APENAS para grid (.row, .col-*) e reset --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- CSS proprietário: sobrescreve tudo visual --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @livewireStyles
    @stack('styles')
</head>
<body class="nf-body">

    {{-- ═══════ NAVBAR ═══════ --}}
    <nav class="nf-navbar">
        <div class="nf-navbar-inner">
            <a href="{{ route('dashboard') }}" class="nf-logo">
                <span class="nf-logo-icon">▶</span>
                <span class="nf-logo-text">Next<span class="nf-logo-accent">Frame</span></span>
            </a>

            <div class="nf-navbar-links d-none d-lg-flex">
                <a href="{{ route('dashboard') }}" class="nf-nav-link @yield('nav_dashboard')">Dashboard</a>
                <a href="{{ route('hardware.index') }}" class="nf-nav-link @yield('nav_hardware')">Meu Setup</a>
                <a href="{{ route('catalog.index') }}" class="nf-nav-link @yield('nav_catalog')">Catálogo</a>
                <a href="{{ route('bottleneck.index') }}" class="nf-nav-link @yield('nav_bottleneck')">Bottleneck</a>
            </div>

            <div class="nf-navbar-right">
                {{-- Avatar dropdown --}}
                <div x-data="{ open: false }" class="nf-avatar-wrap">
                    <button @click="open = !open" class="nf-avatar-btn">
                        <span class="nf-avatar-initials">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        <i class="bi bi-chevron-down nf-avatar-chevron" :class="{ 'rotated': open }"></i>
                    </button>
                    <div class="nf-dropdown" x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         style="display:none;">
                        <div class="nf-dropdown-header">{{ auth()->user()->name }}</div>
                        <div class="nf-dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nf-dropdown-item nf-dropdown-item--danger">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Hamburger mobile --}}
                <button class="nf-hamburger d-lg-none" @click="$dispatch('toggle-sidebar')">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>

    {{-- ═══════ SIDEBAR + MAIN ═══════ --}}
    <div class="nf-layout" x-data="{ sidebar: false }" @toggle-sidebar.window="sidebar = !sidebar">

        {{-- Sidebar --}}
        <aside class="nf-sidebar" :class="{ 'nf-sidebar--open': sidebar }">
            <nav class="nf-sidebar-nav">
                <div class="nf-sidebar-section">
                    <span class="nf-sidebar-label">Análise</span>
                    <a href="{{ route('dashboard') }}" class="nf-sidebar-link @yield('nav_dashboard')">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('bottleneck.index') }}" class="nf-sidebar-link @yield('nav_bottleneck')">
                        <i class="bi bi-activity"></i> Bottleneck
                    </a>
                    <a href="{{ route('upgrade.index') }}" class="nf-sidebar-link @yield('nav_upgrade')">
                        <i class="bi bi-arrow-up-circle"></i> Upgrades
                    </a>
                </div>
                <div class="nf-sidebar-section">
                    <span class="nf-sidebar-label">Hardware</span>
                    <a href="{{ route('hardware.index') }}" class="nf-sidebar-link @yield('nav_hardware')">
                        <i class="bi bi-cpu"></i> Meu Setup
                    </a>
                    <a href="{{ route('catalog.index') }}" class="nf-sidebar-link @yield('nav_catalog')">
                        <i class="bi bi-database"></i> Catálogo
                    </a>
                    <a href="{{ route('compare.index') }}" class="nf-sidebar-link @yield('nav_compare')">
                        <i class="bi bi-layout-split"></i> Comparar
                    </a>
                </div>
            </nav>
        </aside>

        {{-- Overlay mobile --}}
        <div class="nf-sidebar-overlay" x-show="sidebar" @click="sidebar = false" style="display:none;"></div>

        {{-- Conteúdo --}}
        <main class="nf-main">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            @if(session('error'))
                <x-alert type="danger" :message="session('error')" />
            @endif
            @if(session('warning'))
                <x-alert type="warning" :message="session('warning')" />
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Scripts --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
