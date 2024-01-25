<?php

namespace App\Models;

use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfilmentCustomer extends Model
{
    protected $guarded = [];
    protected $casts   = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }
}
