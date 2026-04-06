<?php

namespace App\Livewire\Hardware;

use Livewire\Component;
use Livewire\WithPagination;

class HardwareManager extends Component
{
    use WithPagination;

    public ?int $confirmingDeleteId = null;
    public ?int $editingId = null;

    protected $listeners = [
        'component-saved' => 'handleComponentSaved',
    ];

    public function handleComponentSaved(): void
    {
        $this->dispatch('close-panel');
        session()->flash('success', 'Componente salvo com sucesso!');
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteComponent(int $id): void
    {
        // Implementar quando o Model estiver disponível
        // UserComponent::where('user_id', auth()->id())->where('id', $id)->delete();
        $this->confirmingDeleteId = null;
        session()->flash('success', 'Componente removido.');
    }

    public function editComponent(int $id): void
    {
        $this->editingId = $id;
        $this->dispatch('open-panel', id: $id);
    }

    public function render()
    {
        // Stub: retorna coleção vazia até o Model estar disponível
        $components = collect([]);

        return view('livewire.hardware.hardware-manager', [
            'components' => $components,
        ]);
    }
}
