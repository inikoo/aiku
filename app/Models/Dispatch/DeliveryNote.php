<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Dispatch\DeliveryNote\DeliveryNoteTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Catalogue\Shop;
use App\Models\OMS\Order;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dispatch\DeliveryNote
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property int $shop_id
 * @property int $customer_id
 * @property string $number
 * @property DeliveryNoteTypeEnum $type
 * @property DeliveryNoteStateEnum $state
 * @property DeliveryNoteStatusEnum $status
 * @property bool|null $can_dispatch
 * @property bool|null $restocking
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $weight
 * @property int $number_stocks
 * @property int $number_picks
 * @property int|null $picker_id Main picker
 * @property int|null $packer_id Main packer
 * @property Carbon $date
 * @property string|null $submitted_at
 * @property Carbon|null $assigned_at
 * @property Carbon|null $picking_at
 * @property Carbon|null $picked_at
 * @property Carbon|null $packing_at
 * @property Carbon|null $packed_at
 * @property string|null $finalised_at
 * @property Carbon|null $dispatched_at
 * @property Carbon|null $cancelled_at
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Customer $customer
 * @property-read Collection<int, \App\Models\Dispatch\DeliveryNoteItem> $deliveryNoteItems
 * @property-read Group $group
 * @property-read Collection<int, Order> $orders
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Dispatch\Shipment> $shipments
 * @property-read Shop $shop
 * @property-read \App\Models\Dispatch\DeliveryNoteStats|null $stats
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Dispatch\DeliveryNoteFactory factory($count = null, $state = [])
 * @method static Builder|DeliveryNote newModelQuery()
 * @method static Builder|DeliveryNote newQuery()
 * @method static Builder|DeliveryNote onlyTrashed()
 * @method static Builder|DeliveryNote query()
 * @method static Builder|DeliveryNote withTrashed()
 * @method static Builder|DeliveryNote withoutTrashed()
 * @mixin Eloquent
 */
class DeliveryNote extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasAddresses;
    use HasUniversalSearch;
    use HasFactory;
    use InCustomer;

    protected $casts = [
        'data'   => 'array',
        'state'  => DeliveryNoteStateEnum::class,
        'type'   => DeliveryNoteTypeEnum::class,
        'status' => DeliveryNoteStatusEnum::class,

        'date'               => 'datetime',
        'order_submitted_at' => 'datetime',
        'assigned_at'        => 'datetime',
        'picking_at'         => 'datetime',
        'picked_at'          => 'datetime',
        'packing_at'         => 'datetime',
        'packed_at'          => 'datetime',
        'dispatched_at'      => 'datetime',
        'cancelled_at'       => 'datetime',

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function orders(): MorphToMany
    {
        return $this->morphedByMany(Order::class, 'delivery_noteable');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(DeliveryNoteStats::class);
    }

    public function deliveryNoteItems(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function shipments(): BelongsToMany
    {
        return $this->belongsToMany(Shipment::class);
    }
}
