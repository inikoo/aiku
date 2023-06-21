<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 10:46:32 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\CRM\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Market\Shop;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\SlugOptions;

trait HasOrder
{
    use HasTenantAddress;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerClient(): BelongsTo
    {
        return $this->belongsTo(CustomerClient::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class)->withTimestamps();
    }
}
