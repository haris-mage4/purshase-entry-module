<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Purchases</h1>
        @if ($canManage)
            <a
                href="{{ route('purchases.create') }}"
                class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
                New purchase
            </a>
        @endif
    </div>

    @if ($purchases->isEmpty())
        <p class="rounded border border-gray-300 bg-white p-6 text-center text-gray-600">
            No purchases found.
        </p>
    @else
        <div class="space-y-6">
            @foreach ($purchases as $purchase)
                <div class="overflow-hidden rounded-lg border border-gray-300 bg-white shadow-sm" wire:key="purchase-{{ $purchase->id }}">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3">
                        <div>
                            <span class="font-semibold text-gray-900">Purchase #{{ $purchase->id }}</span>
                            <span class="ml-3 text-sm text-gray-500">
                                {{ $purchase->created_at?->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-bold text-gray-900">
                                ${{ number_format($purchase->total, 2) }}
                            </span>
                            @if ($canManage)
                                <a
                                    href="{{ route('purchases.edit', $purchase) }}"
                                    class="rounded bg-indigo-500 px-3 py-1 text-sm text-white hover:bg-indigo-600"
                                >
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    wire:click="delete({{ $purchase->id }})"
                                    wire:confirm="Delete this purchase?"
                                    class="rounded bg-red-500 px-3 py-1 text-sm text-white hover:bg-red-600"
                                >
                                    Delete
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($purchase->purchaseItems->isEmpty())
                        <p class="p-4 text-sm text-gray-500">No line items.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 text-left">
                                    <th class="border-b p-2">Item</th>
                                    <th class="border-b p-2">Brand</th>
                                    <th class="border-b p-2">Qty</th>
                                    <th class="border-b p-2">Price</th>
                                    <th class="border-b p-2">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->purchaseItems as $line)
                                    <tr wire:key="line-{{ $line->id }}">
                                        <td class="border-b p-2">{{ $line->item?->name ?? '—' }}</td>
                                        <td class="border-b p-2">{{ $line->brand?->name ?? '—' }}</td>
                                        <td class="border-b p-2">{{ $line->qty }}</td>
                                        <td class="border-b p-2">${{ number_format($line->price, 2) }}</td>
                                        <td class="border-b p-2">
                                            ${{ number_format($line->qty * $line->price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
