<?php

namespace Database\Seeders;

use App\Services\LegacyPurchaseImporter;
use Illuminate\Database\Seeder;

class LegacyPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $legacyPurchases = [
            [
                'item_name' => 'Sugar',
                'brand_name' => 'ABC',
                'qty' => 10,
                'price' => 100,
            ],
        ];

        $result = app(LegacyPurchaseImporter::class)->import($legacyPurchases);

        $this->command?->info($result['message']);
    }
}
