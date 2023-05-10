<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:52:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStatusEnum;
use App\Models\Traits\HasAddress;
use App\Models\Traits\UsesGroupConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\SupplierDelivery
 *
 * @property int $id
 * @property string $slug
 * @property int $provider_id
 * @property string $provider_type
 * @property string $number
 * @property SupplierDeliveryStateEnum $state
 * @property SupplierDeliveryStatusEnum $status
 * @property string $date latest relevant date
 * @property string|null $dispatched_at
 * @property string|null $received_at
 * @property string|null $checked_at
 * @property string|null $settled_at
 * @property string|null $cancelled_at
 * @property int $number_of_items
 * @property float|null $total_weight
 * @property string|null $cost_items
 * @property string|null $cost_extra
 * @property string|null $cost_shipping
 * @property string|null $cost_duties
 * @property string $cost_tax
 * @property string $cost_total
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\SupplierDeliveryItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\PurchaseOrder> $purchaseOrders
 * @method static Builder|SupplierDelivery newModelQuery()
 * @method static Builder|SupplierDelivery newQuery()
 * @method static Builder|SupplierDelivery onlyTrashed()
 * @method static Builder|SupplierDelivery query()
 * @method static Builder|SupplierDelivery withTrashed()
 * @method static Builder|SupplierDelivery withoutTrashed()
 * @mixin \Eloquent
 */
class SupplierDelivery extends Model
{
    use UsesGroupConnection;
    use SoftDeletes;
    use HasAddress;
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

    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierDeliveryItem::class);
    }
}
