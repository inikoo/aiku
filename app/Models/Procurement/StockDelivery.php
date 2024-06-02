<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:52:15 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Enums\Procurement\StockDelivery\StockDeliveryStatusEnum;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasHistory;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $parent_type OrgAgent|OrgSupplier|Organisation(intra-group sales)
 * @property int $parent_id
 * @property string $parent_code Parent code on the time of consolidation
 * @property string $parent_name Parent name on the time of consolidation
 * @property string $number
 * @property StockDeliveryStateEnum $state
 * @property StockDeliveryStatusEnum $status
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
 * @property int|null $agent_id
 * @property int|null $supplier_id
 * @property int|null $partner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Address|null $address
 * @property-read Collection<int, Address> $addresses
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $attachments
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Collection<int, \App\Models\Procurement\StockDeliveryItem> $items
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $media
 * @property-read Organisation $organisation
 * @property-read Model|\Eloquent $parent
 * @property-read Collection<int, \App\Models\Procurement\PurchaseOrder> $purchaseOrders
 * @method static \Database\Factories\Procurement\StockDeliveryFactory factory($count = null, $state = [])
 * @method static Builder|StockDelivery newModelQuery()
 * @method static Builder|StockDelivery newQuery()
 * @method static Builder|StockDelivery onlyTrashed()
 * @method static Builder|StockDelivery query()
 * @method static Builder|StockDelivery withTrashed()
 * @method static Builder|StockDelivery withoutTrashed()
 * @mixin Eloquent
 */
class StockDelivery extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasAddress;
    use HasAddresses;
    use HasSlug;
    use HasFactory;
    use InOrganisation;
    use HasAttachments;
    use HasHistory;


    protected $casts = [
        'data'  => 'array',
        'state' => StockDeliveryStateEnum::class,
        'status'=> StockDeliveryStatusEnum::class,
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
        return $this->hasMany(StockDeliveryItem::class);
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }
}
