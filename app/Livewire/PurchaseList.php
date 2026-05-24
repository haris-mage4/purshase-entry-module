<?php

namespace App\Livewire;

use App\Models\Purchase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseList extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', Purchase::class);
    }

    public function delete(int $purchaseId): void
    {
        $purchase = Purchase::query()->findOrFail($purchaseId);

        $this->authorize('delete', $purchase);

        $purchase->delete();

        session()->flash('success', 'Purchase deleted successfully.');
    }

    public function render()
    {
        $purchases = Purchase::query()
            ->with(['purchaseItems.item', 'purchaseItems.brand'])
            ->latest('id')
            ->get();

        return view('livewire.purchase-list', [
            'purchases' => $purchases,
            'canManage' => auth()->user()?->canManagePurchases() ?? false,
        ]);
    }
}
