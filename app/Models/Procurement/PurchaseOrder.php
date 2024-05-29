<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:42:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Assets\Currency;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\PurchaseOrder
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
 * @property array $data
 * @property PurchaseOrderStateEnum $state
 * @property PurchaseOrderStatusEnum $status
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
 * @property string $group_exchange
 * @property string $org_exchange
 * @property int $number_of_items
 * @property float|null $gross_weight
 * @property float|null $net_weight
 * @property string|null $cost_items
 * @property string|null $cost_extra
 * @property string|null $cost_shipping
 * @property string|null $cost_duties
 * @property string $cost_tax
 * @property string $cost_total
 * @property int|null $agent_id
 * @property int|null $supplier_id
 * @property int|null $partner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Address|null $address
 * @property-read Collection<int, Address> $addresses
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Currency $currency
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Collection<int, \App\Models\Procurement\PurchaseOrderItem> $items
 * @property-read Organisation $organisation
 * @property-read Model|\Eloquent $parent
 * @method static \Database\Factories\Procurement\PurchaseOrderFactory factory($count = null, $state = [])
 * @method static Builder|PurchaseOrder newModelQuery()
 * @method static Builder|PurchaseOrder newQuery()
 * @method static Builder|PurchaseOrder onlyTrashed()
 * @method static Builder|PurchaseOrder query()
 * @method static Builder|PurchaseOrder withTrashed()
 * @method static Builder|PurchaseOrder withoutTrashed()
 * @mixin Eloquent
 */
class PurchaseOrder extends Model implements Auditable
{
    use SoftDeletes;
    use HasAddress;
    use HasAddresses;
    use HasSlug;
    use HasFactory;
    use HasHistory;
    use InOrganisation;


    protected $casts = [
        'data'   => 'array',
        'state'  => PurchaseOrderStateEnum::class,
        'status' => PurchaseOrderStatusEnum::class
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

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
