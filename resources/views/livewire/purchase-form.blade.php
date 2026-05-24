<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $isEditing ? 'Edit purchase #'.$purchaseId : 'New purchase' }}
        </h1>
        <a href="{{ route('purchases.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
            ← Back to list
        </a>
    </div>

<div x-data="purchaseForm()" class="rounded-lg border border-gray-300 bg-white p-4">
    <p
        x-show="rows.length === 0"
        class="mb-4 rounded border border-gray-300 bg-gray-50 p-4 text-center text-gray-600"
    >
        There are no purchase items.
    </p>

    <table x-show="rows.length > 0" class="w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2 text-left">Item</th>
                <th class="border p-2 text-left">Brand</th>
                <th class="border p-2 text-left">Qty</th>
                <th class="border p-2 text-left">Price</th>
                <th class="border p-2 text-left">Subtotal</th>
                <th class="border p-2 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, index) in rows" :key="index">
                <tr>
                    <td class="border p-2 align-top">
                        <select
                            x-model="row.item_id"
                            class="w-full rounded border p-1"
                            :class="fieldErrorClass(index, 'item_id')"
                        >
                            <option value="">Select item</option>
                            <template x-for="[id, name] in itemOptions" :key="id">
                                <option :value="String(id)" x-text="name"></option>
                            </template>
                        </select>
                        <p
                            x-show="fieldError(index, 'item_id')"
                            x-text="fieldError(index, 'item_id')"
                            class="mt-1 text-sm text-red-600"
                        ></p>
                    </td>

                    <td class="border p-2 align-top">
                        <select
                            x-model="row.brand_id"
                            class="w-full rounded border p-1"
                            :class="fieldErrorClass(index, 'brand_id')"
                        >
                            <option value="">Select brand</option>
                            <template x-for="[id, name] in brandOptions" :key="id">
                                <option :value="String(id)" x-text="name"></option>
                            </template>
                        </select>
                        <p
                            x-show="fieldError(index, 'brand_id')"
                            x-text="fieldError(index, 'brand_id')"
                            class="mt-1 text-sm text-red-600"
                        ></p>
                    </td>

                    <td class="border p-2 align-top">
                        <input
                            type="number"
                            min="1"
                            x-model.number="row.qty"
                            class="w-full rounded border p-1"
                            :class="fieldErrorClass(index, 'qty')"
                        >
                        <p
                            x-show="fieldError(index, 'qty')"
                            x-text="fieldError(index, 'qty')"
                            class="mt-1 text-sm text-red-600"
                        ></p>
                    </td>

                    <td class="border p-2 align-top">
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            x-model.number="row.price"
                            class="w-full rounded border p-1"
                            :class="fieldErrorClass(index, 'price')"
                        >
                        <p
                            x-show="fieldError(index, 'price')"
                            x-text="fieldError(index, 'price')"
                            class="mt-1 text-sm text-red-600"
                        ></p>
                    </td>

                    <td class="border p-2 align-middle">
                        $<span x-text="formatMoney(subtotal(index))"></span>
                    </td>

                    <td class="border p-2 text-center align-middle">
                        <button
                            type="button"
                            x-on:click="removeRow(index)"
                            class="rounded bg-red-500 px-2 py-1 text-white"
                        >
                            Remove
                        </button>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>

    <div class="mt-4 flex flex-wrap gap-3">
        <button
            type="button"
            x-on:click="addRow()"
            class="rounded bg-blue-500 px-4 py-2 text-white"
        >
            Add row
        </button>

        <button
            type="button"
            x-on:click="$wire.save()"
            class="rounded bg-green-600 px-4 py-2 text-white"
        >
            {{ $isEditing ? 'Update purchase' : 'Save purchase' }}
        </button>

        @if ($isEditing)
            <button
                type="button"
                wire:click="delete"
                wire:confirm="Delete this purchase?"
                class="rounded bg-red-600 px-4 py-2 text-white"
            >
                Delete purchase
            </button>
        @endif
    </div>

    @error('rows')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="mt-4 text-xl font-bold">
        Total: $<span x-text="formatMoney(grandTotal)"></span>
    </div>
</div>
</div>

@script
<script>
    Alpine.data('purchaseForm', () => ({
        items: @js($items),
        brands: @js($brands),
        rows: $wire.entangle('rows').live,
        rowErrors: $wire.entangle('rowErrors').live,

        get itemOptions() {
            return Object.entries(this.items)
        },

        get brandOptions() {
            return Object.entries(this.brands)
        },

        get grandTotal() {
            return this.rows.reduce(
                (sum, row) => sum + this.subtotalFor(row),
                0
            )
        },

        emptyRow() {
            return { item_id: '', brand_id: '', qty: 1, price: 0 }
        },

        addRow() {
            this.rows.push(this.emptyRow())
        },

        removeRow(index) {
            this.rows.splice(index, 1)
        },

        subtotal(index) {
            return this.subtotalFor(this.rows[index])
        },

        subtotalFor(row) {
            const qty = Number(row?.qty) || 0
            const price = Number(row?.price) || 0

            return qty * price
        },

        combinationKey(row) {
            if (!row?.item_id || !row?.brand_id) {
                return null
            }

            return `${row.item_id}-${row.brand_id}`
        },

        isDuplicate(index) {
            const key = this.combinationKey(this.rows[index])

            if (!key) {
                return false
            }

            return this.rows.some(
                (row, i) => i !== index && this.combinationKey(row) === key
            )
        },

        clientError(index, field) {
            const row = this.rows[index]

            if (!row) {
                return null
            }

            if (this.isDuplicate(index) && (field === 'item_id' || field === 'brand_id')) {
                return 'Duplicate item + brand combination.'
            }

            if (field === 'item_id' && row.item_id === '') {
                return 'Item is required.'
            }

            if (field === 'brand_id' && row.brand_id === '') {
                return 'Brand is required.'
            }

            if (field === 'qty') {
                const qty = Number(row.qty)

                if (row.qty === '' || Number.isNaN(qty)) {
                    return 'Quantity is required.'
                }

                if (qty < 1) {
                    return 'Quantity must be at least 1.'
                }
            }

            if (field === 'price') {
                const price = Number(row.price)

                if (row.price === '' || Number.isNaN(price)) {
                    return 'Price is required.'
                }

                if (price < 0) {
                    return 'Price cannot be negative.'
                }
            }

            return null
        },

        serverError(index, field) {
            return this.rowErrors?.[index]?.[field] ?? null
        },

        fieldError(index, field) {
            return this.clientError(index, field) ?? this.serverError(index, field)
        },

        fieldErrorClass(index, field) {
            return this.fieldError(index, field) ? 'border-red-500' : ''
        },

        formatMoney(value) {
            return Number(value || 0).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })
        },
    }))
</script>
@endscript
