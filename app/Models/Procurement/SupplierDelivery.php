<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:52:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStatusEnum;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddresses;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\SupplierDelivery
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $parent_type
 * @property int $parent_id
 * @property string $org_parent_type
 * @property int $org_parent_id
 * @property string $number
 * @property SupplierDeliveryStateEnum $state
 * @property SupplierDeliveryStatusEnum $status
 * @property string $date latest relevant date
 * @property string|null $creating_at
 * @property string|null $dispatched_at
 * @property string|null $received_at
 * @property string|null $checked_at
 * @property string|null $settled_at
 * @property string|null $cancelled_at
 * @property int $number_of_items
 * @property float|null $gross_weight
 * @property float|null $net_weight
 * @property string|null $cost_items
 * @property string|null $cost_extra
 * @property string|null $cost_shipping
 * @property string|null $cost_duties
 * @property string $cost_tax
 * @property string $cost_total
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, \App\Models\Procurement\SupplierDeliveryItem> $items
 * @property-read Organisation $organisation
 * @property-read Model|\Eloquent $provider
 * @property-read Collection<int, \App\Models\Procurement\PurchaseOrder> $purchaseOrders
 * @method static \Database\Factories\Procurement\SupplierDeliveryFactory factory($count = null, $state = [])
 * @method static Builder|SupplierDelivery newModelQuery()
 * @method static Builder|SupplierDelivery newQuery()
 * @method static Builder|SupplierDelivery onlyTrashed()
 * @method static Builder|SupplierDelivery query()
 * @method static Builder|SupplierDelivery withTrashed()
 * @method static Builder|SupplierDelivery withoutTrashed()
 * @mixin Eloquent
 */
class SupplierDelivery extends Model
{
    use SoftDeletes;
    use HasAddresses;
    use HasSlug;
    use HasFactory;

    protected $casts = [
        'data'  => 'array',
        'state' => SupplierDeliveryStateEnum::class,
        'status'=> SupplierDeliveryStatusEnum::class,
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }


    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierDeliveryItem::class);
    }

    public function provider(): MorphTo
    {
        return $this->morphTo();
    }
}
