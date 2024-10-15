<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-16h-07m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Reminder;

use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackToStockReminder extends Model
{
    use InShop;

    protected $casts = [
        'unreminded_at'  => 'datetime'
    ];

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
