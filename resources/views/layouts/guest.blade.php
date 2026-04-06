<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NextFrame')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body class="nf-guest-body">

    <nav class="nf-guest-navbar">
        <a href="{{ route('welcome') }}" class="nf-logo">
            <span class="nf-logo-icon">▶</span>
            <span class="nf-logo-text">Next<span class="nf-logo-accent">Frame</span></span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('login') }}" class="nf-btn-ghost nf-btn-sm">Entrar</a>
            <a href="{{ route('register') }}" class="nf-btn nf-btn-sm">Criar conta</a>
        </div>
    </nav>

    @yield('content')

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
