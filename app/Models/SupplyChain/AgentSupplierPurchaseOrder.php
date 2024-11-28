<?php
/*
 * author Arya Permana - Kirin
 * created on 28-11-2024-10h-34m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\SupplyChain;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Helpers\Currency;
use App\Models\Helpers\Issue;
use App\Models\Helpers\UniversalSearch;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\StockDelivery;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InGroup;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


class AgentSupplierPurchaseOrder extends Model implements HasMedia, Auditable
{
    use SoftDeletes;
    use HasAddress;
    use HasAddresses;
    use HasSlug;
    use HasHistory;
    use InGroup;
    use HasAttachments;
    use HasUniversalSearch;

    protected $casts = [
        'data'            => 'array',
        'cost_data'       => 'array',
        'state'           => PurchaseOrderStateEnum::class,
        'delivery_status' => PurchaseOrderDeliveryStatusEnum::class,
        'date'            => 'datetime',
        'submitted_at'    => 'datetime',
        'confirmed_at'    => 'datetime',
        'manufactured_at' => 'datetime',
        'dispatched_at'   => 'datetime',
        'received_at'     => 'datetime',
        'checked_at'      => 'datetime',
        'settled_at'      => 'datetime',
        'cancelled_at'    => 'datetime',
        'fetched_at'      => 'datetime',
        'last_fetched_at' => 'datetime',
    ];

    protected $attributes = [
        'data'     => '{}',
        'cost_data' => '{}',
        'sources' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function generateTags(): array
    {
        return [
            'supply-chain'
        ];
    }

    public function purchaseOrder():BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function currency():BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function supplier():BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
