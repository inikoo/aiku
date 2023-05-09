<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:42:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\Traits\HasAddress;
use App\Models\Traits\UsesGroupConnection;
use Database\Factories\Procurement\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\PurchaseOrder
 *
 * @property int $id
 * @property string $slug
 * @property int $provider_id
 * @property string $provider_type
 * @property string $number
 * @property array $data
 * @property string $state
 * @property string $status
 * @property string $date latest relevant date
 * @property string|null $submitted_at
 * @property string|null $confirmed_at
 * @property string|null $manufactured_at
 * @property string|null $dispatched_at
 * @property string|null $received_at
 * @property string|null $checked_at
 * @property string|null $settled_at
 * @property string|null $cancelled_at
 * @property int $currency_id
 * @property string $exchange
 * @property int $number_of_items
 * @property float|null $total_weight
 * @property string|null $cost_items
 * @property string|null $cost_extra
 * @property string|null $cost_shipping
 * @property string|null $cost_duties
 * @property string $cost_tax
 * @property string $cost_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\PurchaseOrderItem> $items
 * @property-read Model|\Eloquent $provider
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\SupplierDelivery> $supplierDeliveries
 * @method static PurchaseOrderFactory factory($count = null, $state = [])
 * @method static Builder|PurchaseOrder newModelQuery()
 * @method static Builder|PurchaseOrder newQuery()
 * @method static Builder|PurchaseOrder onlyTrashed()
 * @method static Builder|PurchaseOrder query()
 * @method static Builder|PurchaseOrder withTrashed()
 * @method static Builder|PurchaseOrder withoutTrashed()
 * @mixin \Eloquent
 */
class PurchaseOrder extends Model
{
    use UsesGroupConnection;
    use SoftDeletes;
    use HasAddress;
    use HasSlug;
    use HasFactory;


    protected $casts = [
        'data' => 'array',
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

    public function provider(): MorphTo
    {
        return $this->morphTo();
    }

    public function supplierDeliveries(): BelongsToMany
    {
        return $this->belongsToMany(SupplierDelivery::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
