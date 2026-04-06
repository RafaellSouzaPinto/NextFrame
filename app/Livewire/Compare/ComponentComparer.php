<?php

namespace App\Livewire\Compare;

use Livewire\Component;

class ComponentComparer extends Component
{
    public ?int $leftId = null;
    public ?int $rightId = null;
    public string $typeFilter = 'gpu';

    public ?array $leftComponent = null;
    public ?array $rightComponent = null;
    public array $diffTable = [];
    public ?string $verdict = null;

    public array $componentOptions = [];

    public function mount(?int $left = null, ?int $right = null, string $type = 'gpu'): void
    {
        $this->leftId = $left;
        $this->rightId = $right;
        $this->typeFilter = $type;

        $this->loadOptions();

        if ($this->leftId) {
            $this->loadLeft();
        }
        if ($this->rightId) {
            $this->loadRight();
        }
        if ($this->leftComponent && $this->rightComponent) {
            $this->buildDiff();
        }
    }

    public function updatedLeftId(): void
    {
        $this->loadLeft();
        $this->buildDiff();
    }

    public function updatedRightId(): void
    {
        $this->loadRight();
        $this->buildDiff();
    }

    public function updatedTypeFilter(): void
    {
        $this->leftId = null;
        $this->rightId = null;
        $this->leftComponent = null;
        $this->rightComponent = null;
        $this->diffTable = [];
        $this->verdict = null;
        $this->loadOptions();
    }

    public function swapComponents(): void
    {
        [$this->leftId, $this->rightId] = [$this->rightId, $this->leftId];
        [$this->leftComponent, $this->rightComponent] = [$this->rightComponent, $this->leftComponent];
        $this->buildDiff();
    }

    private function loadOptions(): void
    {
        // Stub — implementar com query ao Model de catálogo
        $this->componentOptions = [];
    }

    private function loadLeft(): void
    {
        if (!$this->leftId) {
            $this->leftComponent = null;
            return;
        }
        // Stub — buscar componente por ID no catálogo
        $this->leftComponent = null;
    }

    private function loadRight(): void
    {
        if (!$this->rightId) {
            $this->rightComponent = null;
            return;
        }
        // Stub — buscar componente por ID no catálogo
        $this->rightComponent = null;
    }

    private function buildDiff(): void
    {
        if (!$this->leftComponent || !$this->rightComponent) {
            $this->diffTable = [];
            $this->verdict = null;
            return;
        }

        // Stub — calcular diferença real quando os Models existirem
        $this->diffTable = [];
        $this->verdict = null;
    }

    public function render()
    {
        return view('livewire.compare.component-comparer');
    }
}
