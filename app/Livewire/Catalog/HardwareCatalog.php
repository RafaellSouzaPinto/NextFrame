<?php

namespace App\Livewire\Catalog;

use Livewire\Component;
use Livewire\WithPagination;

class HardwareCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = 'all';
    public string $sortBy = 'name';
    public int $totalCount = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function setType(string $type): void
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $this->sortBy = $sort;
        $this->resetPage();
    }

    public function addToMySetup(int $catalogId): void
    {
        // Implementar quando o Model estiver disponível
        session()->flash('success', 'Componente adicionado ao seu setup!');
    }

    public function render()
    {
        // Stub: retorna coleção vazia até o Model estar disponível
        $components = collect([]);
        $this->totalCount = 0;

        return view('livewire.catalog.hardware-catalog', [
            'components' => $components,
        ]);
    }
}
