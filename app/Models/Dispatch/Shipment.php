<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Dispatch\Shipment
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $shipper_id
 * @property int|null $customer_id
 * @property string $status
 * @property string|null $reference
 * @property string|null $tracking
 * @property string|null $error_message
 * @property array $data
 * @property string|null $shipped_at
 * @property string|null $tracked_at
 * @property int $tracked_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\DeliveryNote> $deliveryNotes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\ShipmentEvent> $events
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \App\Models\Dispatch\Shipper|null $shipper
 * @property-read Shop|null $shop
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Dispatch\ShipmentFactory factory($count = null, $state = [])
 * @method static Builder|Shipment newModelQuery()
 * @method static Builder|Shipment newQuery()
 * @method static Builder|Shipment onlyTrashed()
 * @method static Builder|Shipment query()
 * @method static Builder|Shipment withTrashed()
 * @method static Builder|Shipment withoutTrashed()
 * @mixin Eloquent
 */
class Shipment extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasFactory;
    use InCustomer;

    protected $casts = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];


    public function shipper(): BelongsTo
    {
        return $this->belongsTo(Shipper::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }


    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class);
    }


}
