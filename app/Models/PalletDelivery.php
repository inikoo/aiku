<?php

namespace App\Models;

use App\Enums\Inventory\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Pallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PalletDelivery extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'state' => PalletDeliveryStateEnum::class,
        'in_at' => 'datetime',
        'out_at' => 'datetime',
        'data' => 'array'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class);
    }
}
