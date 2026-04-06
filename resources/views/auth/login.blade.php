@extends('layouts.guest')

@section('title', 'Entrar — NextFrame')

@section('content')
<div class="nf-auth-wrap">

    {{-- Form centralizado --}}
    <div class="nf-auth-right">
        <div class="nf-auth-form">
            <a href="{{ route('welcome') }}" class="nf-auth-logo">
                <span class="nf-logo-icon">▶</span>
                <span class="nf-logo-text">Next<span class="nf-logo-accent">Frame</span></span>
            </a>

            <div class="nf-auth-title">Bem-vindo de volta</div>
            <div class="nf-auth-sub">Entre na sua conta para continuar.</div>

            @if ($errors->any())
                <div class="nf-alert nf-alert--danger mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="nf-form-group">
                    <label class="nf-label" for="email">E-mail</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="nf-input @error('email') is-invalid @enderror"
                        placeholder="seu@email.com"
                        required
                        autofocus
                    >
                </div>

                <div class="nf-form-group">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="nf-label" for="password" style="margin:0;">Senha</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" style="font-size:12px; color:var(--nf-accent);">Esqueceu?</a>
                        @endif
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="nf-input @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div style="display:flex; align-items:center; gap:8px; margin-bottom:20px;">
                    <input type="checkbox" name="remember" id="remember" style="accent-color:var(--nf-accent);">
                    <label for="remember" style="font-size:13px; color:var(--nf-text-secondary); cursor:pointer;">
                        Lembrar de mim
                    </label>
                </div>

                <button type="submit" class="nf-btn" style="width:100%; justify-content:center;">
                    Entrar
                </button>
            </form>

            <div class="nf-auth-footer">
                Não tem conta?
                <a href="{{ route('register') }}" style="color:var(--nf-accent);">Criar gratuitamente</a>
            </div>
        </div>
    </div>

</div>
@endsection
