<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class PerformanceDashboard extends Component
{
    public string $resolution = '1080p';

    public array $setup = [];
    public array $scores = [];
    public array $bottleneckSummary = [];
    public int $upgradeCount = 0;
    public array $fpsData = [];

    public function mount(): void
    {
        $this->loadDashboardData();
    }

    public function switchResolution(string $res): void
    {
        $this->resolution = $res;
        $this->loadFpsData();
    }

    private function loadDashboardData(): void
    {
        // Dados stub — serão substituídos quando o backend estiver implementado
        $this->setup = [
            ['type' => 'cpu', 'name' => 'Nenhuma CPU cadastrada', 'score' => 0],
            ['type' => 'gpu', 'name' => 'Nenhuma GPU cadastrada', 'score' => 0],
            ['type' => 'ram', 'name' => 'Nenhuma RAM cadastrada', 'score' => 0],
        ];

        $this->scores = [
            'cpu'         => 0,
            'gpu'         => 0,
            'bottleneck'  => 0,
        ];

        $this->bottleneckSummary = [
            'limited_by' => null,
            'pct'        => 0,
            'severity'   => 'low',
        ];

        $this->upgradeCount = 0;

        $this->loadFpsData();
    }

    private function loadFpsData(): void
    {
        // Dados stub por resolução
        $data = [
            '1080p' => [
                ['game' => 'CS2',        'fps' => 0, 'pct' => 0],
                ['game' => 'Fortnite',   'fps' => 0, 'pct' => 0],
                ['game' => 'Cyberpunk',  'fps' => 0, 'pct' => 0],
                ['game' => 'Valorant',   'fps' => 0, 'pct' => 0],
                ['game' => 'Minecraft',  'fps' => 0, 'pct' => 0],
            ],
            '1440p' => [
                ['game' => 'CS2',        'fps' => 0, 'pct' => 0],
                ['game' => 'Fortnite',   'fps' => 0, 'pct' => 0],
                ['game' => 'Cyberpunk',  'fps' => 0, 'pct' => 0],
                ['game' => 'Valorant',   'fps' => 0, 'pct' => 0],
                ['game' => 'Minecraft',  'fps' => 0, 'pct' => 0],
            ],
            '4K' => [
                ['game' => 'CS2',        'fps' => 0, 'pct' => 0],
                ['game' => 'Fortnite',   'fps' => 0, 'pct' => 0],
                ['game' => 'Cyberpunk',  'fps' => 0, 'pct' => 0],
                ['game' => 'Valorant',   'fps' => 0, 'pct' => 0],
                ['game' => 'Minecraft',  'fps' => 0, 'pct' => 0],
            ],
        ];

        $this->fpsData = $data[$this->resolution] ?? $data['1080p'];
    }

    public function render()
    {
        return view('livewire.dashboard.performance-dashboard');
    }
}
