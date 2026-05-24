<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseForm extends Component
{
    use AuthorizesRequests;

    public ?int $purchaseId = null;

    public array $rows = [];

    public array $items = [];

    public array $brands = [];

    public float $total = 0;

    /** @var array<int, array<string, string>> */
    public array $rowErrors = [];

    public function mount(?Purchase $purchase = null): void
    {
        $this->loadCatalog();

        if ($purchase) {
            $this->authorize('update', $purchase);
            $this->purchaseId = $purchase->id;
            $this->loadRowsFromPurchase($purchase);
        } else {
            $this->authorize('create', Purchase::class);
            $this->rows = [];
        }

        $this->calculateTotal();
    }

    protected function loadCatalog(): void
    {
        $this->items = Item::query()->orderBy('name')->pluck('name', 'id')->all();
        $this->brands = Brand::query()->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function loadRowsFromPurchase(Purchase $purchase): void
    {
        $purchase->load('purchaseItems');

        $this->rows = $purchase->purchaseItems
            ->map(fn ($line) => [
                'item_id' => (string) $line->item_id,
                'brand_id' => (string) $line->brand_id,
                'qty' => (int) $line->qty,
                'price' => (float) $line->price,
            ])
            ->values()
            ->all();
    }

    public function updatedRows(): void
    {
        $this->validateRows();
        $this->calculateTotal();
    }

    public function validateRows(): void
    {
        $this->rowErrors = [];

        if ($this->rows === []) {
            return;
        }

        $combinations = [];

        foreach ($this->rows as $index => $row) {
            $errors = [];

            try {
                $this->validate([
                    "rows.$index.item_id" => ['required', 'exists:items,id'],
                    "rows.$index.brand_id" => ['required', 'exists:brands,id'],
                    "rows.$index.qty" => ['required', 'numeric', 'min:1'],
                    "rows.$index.price" => ['required', 'numeric', 'min:0'],
                ]);
            } catch (ValidationException $e) {
                foreach ($e->validator->errors()->messages() as $field => $messages) {
                    $fieldName = str_replace("rows.$index.", '', $field);
                    $errors[$fieldName] = $messages[0];
                }
            }

            if ($row['item_id'] !== '' && $row['brand_id'] !== '') {
                $key = $row['item_id'].'-'.$row['brand_id'];

                if (in_array($key, $combinations, true)) {
                    $message = 'Duplicate item + brand combination.';
                    $errors['item_id'] = $message;
                    $errors['brand_id'] = $message;
                }

                $combinations[] = $key;
            }

            if ($errors !== []) {
                $this->rowErrors[$index] = $errors;
            }
        }
    }

    public function calculateTotal(): void
    {
        $this->total = collect($this->rows)->sum(
            fn (array $row) => ((float) $row['qty']) * ((float) $row['price'])
        );
    }

    /**
     * @throws \Throwable
     */
    public function save(): void
    {
        if ($this->purchaseId) {
            $purchase = Purchase::query()->findOrFail($this->purchaseId);
            $this->authorize('update', $purchase);
        } else {
            $this->authorize('create', Purchase::class);
            $purchase = null;
        }

        $this->validateRows();

        if ($this->rows === []) {
            $this->addError('rows', 'Add at least one purchase item.');

            return;
        }

        if ($this->rowErrors !== []) {
            return;
        }

        DB::transaction(function () use (&$purchase) {
            if ($purchase) {
                $purchase->update(['total' => $this->total]);
                $purchase->purchaseItems()->delete();
            } else {
                $purchase = Purchase::create(['total' => $this->total]);
                $this->purchaseId = $purchase->id;
            }

            foreach ($this->rows as $row) {
                $purchase->purchaseItems()->create([
                    'item_id' => (int) $row['item_id'],
                    'brand_id' => (int) $row['brand_id'],
                    'qty' => $row['qty'],
                    'price' => $row['price'],
                ]);
            }
        });

        session()->flash('success', 'Purchase saved successfully.');

        $this->redirect(route('purchases.index'), navigate: true);
    }

    public function delete(): void
    {
        if (! $this->purchaseId) {
            return;
        }

        $purchase = Purchase::query()->findOrFail($this->purchaseId);

        $this->authorize('delete', $purchase);

        $purchase->delete();

        session()->flash('success', 'Purchase deleted successfully.');

        $this->redirect(route('purchases.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase-form', [
            'isEditing' => $this->purchaseId !== null,
        ]);
    }
}
