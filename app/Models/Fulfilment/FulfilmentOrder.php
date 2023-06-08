<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 10:41:08 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Models\Dispatch\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Traits\HasOrder;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;

/**
 * App\Models\Fulfilment\FulfilmentOrder
 *
 * @property int $id
 * @property string $slug
 * @property string|null $number
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $customer_client_id
 * @property string $state
 * @property string|null $submitted_at
 * @property string|null $in_warehouse_at
 * @property string|null $finalised_at
 * @property string|null $dispatched_at
 * @property string|null $cancelled_at
 * @property bool|null $is_picking_on_hold
 * @property bool|null $can_dispatch
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Address> $addresses
 * @property-read Customer $customer
 * @property-read CustomerClient|null $customerClient
 * @property-read Collection<int, DeliveryNote> $deliveryNotes
 * @property-read Collection<int, \App\Models\Fulfilment\FulfilmentOrderItem> $items
 * @property-read Shop $shop
 * @property-read \App\Models\Fulfilment\FulfilmentOrderStats|null $stats
 * @method static Builder|FulfilmentOrder newModelQuery()
 * @method static Builder|FulfilmentOrder newQuery()
 * @method static Builder|FulfilmentOrder onlyTrashed()
 * @method static Builder|FulfilmentOrder query()
 * @method static Builder|FulfilmentOrder withTrashed()
 * @method static Builder|FulfilmentOrder withoutTrashed()
 * @mixin Eloquent
 */
class FulfilmentOrder extends Model
{
    use HasOrder;
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;


    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(FulfilmentOrderItem::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(FulfilmentOrderStats::class);
    }
}
