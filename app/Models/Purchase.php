<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['total'];

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
