<?php

namespace App\Console\Commands;

use App\Services\LegacyPurchaseImporter;
use Illuminate\Console\Command;

class ImportLegacyPurchasesCommand extends Command
{
    protected $signature = 'purchases:import-legacy';

    protected $description = 'Import legacy purchase lines (item/brand names) into normalized tables';

    public function handle(LegacyPurchaseImporter $importer): int
    {
        $legacyPurchases = [
            [
                'item_name' => 'Sugar',
                'brand_name' => 'ABC',
                'qty' => 10,
                'price' => 100,
            ],
        ];

        $result = $importer->import($legacyPurchases);

        $this->info($result['message']);

        if ($result['purchase_id']) {
            $this->line("Purchase ID: {$result['purchase_id']}");
        }

        return self::SUCCESS;
    }
}
