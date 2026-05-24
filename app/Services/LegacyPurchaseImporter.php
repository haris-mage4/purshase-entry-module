<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Item;
use App\Models\LegacyImportLog;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class LegacyPurchaseImporter
{
    /**
     * @param array $legacyLines
     * @return array
     * @throws \JsonException
     * @throws \Throwable
     */
    public function import(array $legacyLines): array
    {
        $lines = $this->normalizeLines($legacyLines);

        if ($lines === []) {
            return [
                'status' => 'skipped',
                'purchase_id' => null,
                'message' => 'No legacy lines to import.',
            ];
        }

        $payloadHash = $this->hashPayload($lines);

        $existing = LegacyImportLog::query()
            ->where('payload_hash', $payloadHash)
            ->first();

        if ($existing) {
            return [
                'status' => 'skipped',
                'purchase_id' => $existing->purchase_id,
                'message' => 'This legacy batch was already imported (idempotent skip).',
            ];
        }

        $purchase = DB::transaction(function () use ($lines, $payloadHash) {
            $total = collect($lines)->sum(
                fn (array $line) => $line['qty'] * $line['price']
            );

            $purchase = Purchase::query()->create([
                'total' => $total,
            ]);

            foreach ($lines as $line) {
                $item = Item::query()->firstOrCreate([
                    'name' => $line['item_name'],
                ]);

                $brand = Brand::query()->firstOrCreate([
                    'name' => $line['brand_name'],
                ]);

                $purchase->purchaseItems()->create([
                    'item_id' => $item->id,
                    'brand_id' => $brand->id,
                    'qty' => $line['qty'],
                    'price' => $line['price'],
                ]);
            }

            LegacyImportLog::query()->create([
                'payload_hash' => $payloadHash,
                'purchase_id' => $purchase->id,
            ]);

            return $purchase;
        });

        return [
            'status' => 'imported',
            'purchase_id' => $purchase->id,
            'message' => "Imported purchase #{$purchase->id} with ".count($lines).' line(s).',
        ];
    }

    /**
     * @param array $legacyLines
     * @return array
     */
    protected function normalizeLines(array $legacyLines): array
    {
        return collect($legacyLines)
            ->map(function (array $line) {
                return [
                    'item_name' => trim((string) ($line['item_name'] ?? '')),
                    'brand_name' => trim((string) ($line['brand_name'] ?? '')),
                    'qty' => (int) ($line['qty'] ?? 0),
                    'price' => (float) ($line['price'] ?? 0),
                ];
            })
            ->filter(fn (array $line) => $line['item_name'] !== '' && $line['brand_name'] !== '')
            ->sortBy(fn (array $line) => implode('|', [
                $line['item_name'],
                $line['brand_name'],
                $line['qty'],
                $line['price'],
            ]))
            ->values()
            ->all();
    }

    /**
     * @param array $lines
     * @return string
     * @throws \JsonException
     */
    protected function hashPayload(array $lines): string
    {
        return hash('sha256', json_encode($lines, JSON_THROW_ON_ERROR));
    }
}
