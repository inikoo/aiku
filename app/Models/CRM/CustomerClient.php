<?php

namespace App\Models\CRM;

use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CustomerClient extends Model
{
    protected $casts = [
        'location' => 'array',
        'deactivated_at'=>'datetime'
    ];

    protected $attributes = [
        'location' => '{}',
    ];

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'addressable')->withTimestamps();
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
