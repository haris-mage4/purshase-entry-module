<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Item;
use Illuminate\Database\Seeder;

class PurchaseCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Laptop', 'Keyboard', 'Mouse'] as $name) {
            Item::query()->firstOrCreate(['name' => $name]);
        }

        foreach (['HP', 'Dell', 'Logitech'] as $name) {
            Brand::query()->firstOrCreate(['name' => $name]);
        }
    }
}
