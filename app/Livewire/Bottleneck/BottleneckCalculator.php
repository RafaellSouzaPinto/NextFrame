<?php

namespace App\Livewire\Bottleneck;

use Livewire\Component;

class BottleneckCalculator extends Component
{
    public bool $useOwnSetup = true;
    public ?int $cpuId = null;
    public ?int $gpuId = null;
    public string $resolution = '1440p';
    public bool $loading = false;
    public bool $hasResult = false;

    public array $result = [
        'bottleneck_pct' => 0,
        'limited_by'     => 'cpu',
        'severity'       => 'low',
        'cpu_pct'        => 50,
        'gpu_pct'        => 50,
        'fps_impact'     => 0,
        'explanation'    => '',
        'cpu_name'       => '',
        'gpu_name'       => '',
    ];

    public array $cpuOptions = [];
    public array $gpuOptions = [];

    public function mount(): void
    {
        // Pré-preencher com o setup do usuário quando o Model existir
        // $setup = auth()->user()->components;
        // $this->cpuId = $setup->where('type', 'cpu')->first()?->catalog_id;
        // $this->gpuId = $setup->where('type', 'gpu')->first()?->catalog_id;
    }

    public function toggleUseOwnSetup(): void
    {
        $this->useOwnSetup = !$this->useOwnSetup;
        $this->hasResult = false;
    }

    public function calculate(): void
    {
        $this->loading = true;

        // Stub: algoritmo real será implementado com os dados do catálogo
        // Por ora, simula um resultado para demonstração da UI
        $this->result = [
            'bottleneck_pct' => 23,
            'limited_by'     => 'cpu',
            'severity'       => 'medium',
            'cpu_pct'        => 62,
            'gpu_pct'        => 38,
            'fps_impact'     => -18,
            'explanation'    => 'Sua CPU está processando frames mais lentamente do que sua GPU consegue renderizá-los. Isso significa que sua GPU fica ociosa parte do tempo, aguardando a CPU. Um upgrade de CPU ou overclock pode resolver o gargalo.',
            'cpu_name'       => 'CPU Selecionada',
            'gpu_name'       => 'GPU Selecionada',
        ];

        $this->hasResult = true;
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.bottleneck.bottleneck-calculator');
    }
}
