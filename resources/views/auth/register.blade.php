@extends('layouts.guest')

@section('title', 'Criar conta — NextFrame')

@section('content')
<div class="nf-auth-wrap">

    {{-- Form centralizado --}}
    <div class="nf-auth-right">
        <div class="nf-auth-form">
            <a href="{{ route('welcome') }}" class="nf-auth-logo">
                <span class="nf-logo-icon">▶</span>
                <span class="nf-logo-text">Next<span class="nf-logo-accent">Frame</span></span>
            </a>

            <div class="nf-auth-title">Criar conta</div>
            <div class="nf-auth-sub">Grátis. Sem cartão de crédito.</div>

            @if ($errors->any())
                <div class="nf-alert nf-alert--danger mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="nf-form-group">
                    <label class="nf-label" for="name">Nome</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        class="nf-input @error('name') is-invalid @enderror"
                        placeholder="Seu nome"
                        required
                        autofocus
                    >
                </div>

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
                    >
                </div>

                <div class="nf-form-group">
                    <label class="nf-label" for="password">Senha</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="nf-input @error('password') is-invalid @enderror"
                        placeholder="Mínimo 8 caracteres"
                        required
                    >
                </div>

                <div class="nf-form-group">
                    <label class="nf-label" for="password_confirmation">Confirmar senha</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="nf-input"
                        placeholder="Repita a senha"
                        required
                    >
                </div>

                <button type="submit" class="nf-btn" style="width:100%; justify-content:center;">
                    <i class="bi bi-person-plus"></i>
                    Criar conta
                </button>
            </form>

            <div class="nf-auth-footer">
                Já tem conta?
                <a href="{{ route('login') }}" style="color:var(--nf-accent);">Entrar</a>
            </div>
        </div>
    </div>

</div>
@endsection
