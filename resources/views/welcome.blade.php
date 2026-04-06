@extends('layouts.guest')

@section('title', 'NextFrame — Descubra o que está limitando seu PC')

@section('content')

{{-- HERO --}}
<section class="nf-hero">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="nf-badge nf-badge--accent mb-3">Beta público disponível</span>
                <h1 class="nf-hero-title">
                    Descubra o que está<br>
                    <span class="nf-text-accent">limitando seu PC.</span>
                </h1>
                <p class="nf-hero-sub">
                    Analise o gargalo entre sua CPU e GPU, compare componentes e receba sugestões de upgrade com base no seu orçamento e setup atual.
                </p>
                <div class="nf-hero-actions">
                    <a href="{{ route('register') }}" class="nf-btn">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Analisar meu PC
                    </a>
                    <a href="#como-funciona" class="nf-btn-ghost">
                        Ver como funciona
                        <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                {{-- Mock dashboard estático --}}
                <div class="nf-hero-mock">
                    <div class="nf-mock-toolbar">
                        <span class="nf-mono" style="font-size:11px; color:var(--nf-text-muted);">nextframe://live-analysis</span>
                    </div>
                    <div class="nf-mock-row">
                        <div class="nf-mock-stat">
                            <div class="nf-mock-stat-label">CPU Score</div>
                            <div class="nf-mock-stat-value nf-text-cpu">8.742</div>
                        </div>
                        <div class="nf-mock-stat">
                            <div class="nf-mock-stat-label">GPU Score</div>
                            <div class="nf-mock-stat-value nf-text-gpu">12.381</div>
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span class="nf-bottleneck-label nf-bottleneck-label--cpu">CPU 38%</span>
                            <span class="nf-bottleneck-label nf-bottleneck-label--gpu">GPU 62%</span>
                        </div>
                        <div class="nf-bottleneck-track" style="height:20px;">
                            <div class="nf-bottleneck-fill nf-bottleneck-fill--cpu" style="width:38%;"></div>
                            <div class="nf-bottleneck-fill nf-bottleneck-fill--gpu" style="width:62%;"></div>
                        </div>
                    </div>
                    <div class="nf-fps-graph">
                        @foreach([['CS2', 82], ['Fortnite', 67], ['Cyberpunk', 45], ['Minecraft', 95]] as [$game, $pct])
                        <div class="nf-fps-row">
                            <div class="nf-fps-game">{{ $game }}</div>
                            <div class="nf-fps-bar-wrap">
                                <div class="nf-fps-bar-fill" style="width:{{ $pct }}%;">
                                    <span class="nf-fps-value">{{ round($pct * 1.8) }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section class="nf-feature-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size:28px; font-weight:700; color:var(--nf-text-primary); margin-bottom:10px;">
                Tudo que você precisa para otimizar seu setup
            </h2>
            <p style="color:var(--nf-text-secondary); font-size:15px;">
                Ferramentas inteligentes para gamers e entusiastas de hardware.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="nf-feature-card">
                    <div class="nf-feature-icon nf-text-cpu">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="nf-feature-title">Calculadora de Bottleneck</div>
                    <p class="nf-feature-desc">Identifique com precisão qual componente está limitando sua performance e qual o impacto real em FPS por resolução.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nf-feature-card">
                    <div class="nf-feature-icon nf-text-gpu">
                        <i class="bi bi-layout-split"></i>
                    </div>
                    <div class="nf-feature-title">Comparador de Hardware</div>
                    <p class="nf-feature-desc">Compare dois componentes lado a lado com delta calculado por especificação. Descubra exatamente o quanto vai ganhar.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nf-feature-card">
                    <div class="nf-feature-icon nf-text-accent">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="nf-feature-title">Sugestões de Upgrade</div>
                    <p class="nf-feature-desc">Receba recomendações personalizadas ordenadas por custo-benefício, levando em conta seu setup e orçamento disponível.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- COMO FUNCIONA --}}
<section class="nf-steps-section" id="como-funciona">
    <div class="container">
        <div class="text-center mb-5">
            <h2 style="font-size:28px; font-weight:700; color:var(--nf-text-primary); margin-bottom:10px;">
                Como funciona
            </h2>
        </div>
        <div class="nf-steps-list">
            <div class="nf-steps-item">
                <span class="nf-steps-num">01</span>
                <div>
                    <div class="nf-step-title">Cadastre seu setup</div>
                    <p class="nf-step-desc">Registre sua CPU, GPU, RAM e armazenamento. Busque diretamente no nosso catálogo de componentes.</p>
                </div>
            </div>
            <div class="nf-steps-item">
                <span class="nf-steps-num">02</span>
                <div>
                    <div class="nf-step-title">Analise o bottleneck</div>
                    <p class="nf-step-desc">Nossa engine calcula o gargalo real entre seus componentes e estima o impacto em FPS para diferentes resoluções.</p>
                </div>
            </div>
            <div class="nf-steps-item">
                <span class="nf-steps-num">03</span>
                <div>
                    <div class="nf-step-title">Receba sugestões</div>
                    <p class="nf-step-desc">Veja upgrades recomendados ordenados por custo-benefício. Compare e decida com dados, não com achismo.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA FINAL --}}
<section class="nf-cta-section">
    <div class="container">
        <h2 style="font-size:28px; font-weight:700; color:var(--nf-text-primary); margin-bottom:12px; letter-spacing:-0.3px;">
            Pronto para otimizar seu setup?
        </h2>
        <p style="color:var(--nf-text-secondary); font-size:16px; margin-bottom:28px;">
            Crie sua conta gratuitamente e descubra o potencial real do seu PC.
        </p>
        <a href="{{ route('register') }}" class="nf-btn" style="font-size:15px; padding:11px 28px;">
            <i class="bi bi-person-plus"></i>
            Criar conta grátis
        </a>
    </div>
</section>

@endsection
