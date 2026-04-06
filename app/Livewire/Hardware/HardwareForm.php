<?php

namespace App\Livewire\Hardware;

use Livewire\Attributes\Validate;
use Livewire\Component;

class HardwareForm extends Component
{
    public ?int $componentId = null;

    #[Validate('required|in:cpu,gpu,ram,storage')]
    public string $type = 'gpu';

    #[Validate('required|string|min:2|max:150')]
    public string $name = '';

    public string $catalogSearch = '';
    public array $catalogResults = [];
    public ?int $catalogId = null;

    public function updatedCatalogSearch(): void
    {
        if (strlen($this->catalogSearch) < 2) {
            $this->catalogResults = [];
            return;
        }

        // Stub: será substituído pela query real ao catálogo
        $this->catalogResults = [];
    }

    public function selectFromCatalog(int $id, string $name): void
    {
        $this->catalogId = $id;
        $this->name = $name;
        $this->catalogSearch = $name;
        $this->catalogResults = [];
    }

    public function mount(?int $id = null): void
    {
        $this->componentId = $id;

        if ($id) {
            // Implementar quando o Model estiver disponível
        }
    }

    public function save(): void
    {
        $this->validate();

        // Implementar quando o Model estiver disponível
        // UserComponent::updateOrCreate([...]);

        $this->dispatch('component-saved');
        $this->reset(['name', 'catalogSearch', 'catalogId', 'catalogResults']);
        $this->type = 'gpu';
    }

    public function cancel(): void
    {
        $this->dispatch('close-panel');
        $this->reset(['componentId', 'name', 'catalogSearch', 'catalogId', 'catalogResults']);
    }

    public function render()
    {
        return view('livewire.hardware.hardware-form');
    }
}
