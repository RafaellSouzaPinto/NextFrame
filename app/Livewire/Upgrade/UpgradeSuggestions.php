<?php

namespace App\Livewire\Upgrade;

use Livewire\Component;

class UpgradeSuggestions extends Component
{
    public string $budget = '';
    public string $priorityFilter = 'bottleneck';

    public array $suggestions = [];
    public ?string $currentBottleneck = null;

    public function mount(): void
    {
        $this->loadSuggestions();
    }

    public function updatedBudget(): void
    {
        $this->filterByBudget();
    }

    public function setPriority(string $priority): void
    {
        $this->priorityFilter = $priority;
        $this->loadSuggestions();
    }

    public function filterByBudget(): void
    {
        $this->loadSuggestions();
    }

    private function loadSuggestions(): void
    {
        // Stub — implementar quando o Model e a engine de bottleneck existirem
        // $userSetup = auth()->user()->components;
        // $bottleneck = BottleneckEngine::calculate($userSetup);
        // $this->suggestions = UpgradeEngine::suggest($userSetup, $bottleneck, $this->priorityFilter, $this->budget);

        $this->currentBottleneck = null;
        $this->suggestions = [];
    }

    public function render()
    {
        return view('livewire.upgrade.upgrade-suggestions');
    }
}
