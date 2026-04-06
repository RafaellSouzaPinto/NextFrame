'use strict';

// ─── Bootstrap Tooltips ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));
});

// ─── Helpers globais (acessíveis em expressões Alpine.js) ─────────────────────
window.NF = {

    /** Formata número com separador pt-BR */
    fmt(n) {
        return new Intl.NumberFormat('pt-BR').format(n);
    },

    /** Retorna a variável CSS de cor baseada no % de bottleneck */
    bottleneckColor(pct) {
        if (pct <= 10) return 'var(--nf-green)';
        if (pct <= 25) return 'var(--nf-amber)';
        return 'var(--nf-red)';
    },

    /** Retorna classe de severidade baseada no % */
    bottleneckSeverity(pct) {
        if (pct <= 10) return 'low';
        if (pct <= 25) return 'medium';
        return 'high';
    },

    /** Retorna classe CSS de score */
    scoreClass(score) {
        if (score > 10000) return 'nf-score--high';
        if (score > 5000)  return 'nf-score--mid';
        return 'nf-score--low';
    },
};

// ─── Bridge Livewire → Alpine ─────────────────────────────────────────────────
// Transforma eventos Livewire em CustomEvents do DOM para que
// componentes Alpine (x-data) possam escutar com @event.window
document.addEventListener('livewire:initialized', () => {
    Livewire.on('component-saved', () => {
        document.dispatchEvent(new CustomEvent('component-saved'));
    });

    Livewire.on('close-panel', () => {
        document.dispatchEvent(new CustomEvent('close-panel'));
    });

    Livewire.on('open-panel', (data) => {
        document.dispatchEvent(new CustomEvent('open-panel', { detail: data }));
    });
});
