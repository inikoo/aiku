<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 13 Feb 2024 16:23:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Helpers\Currency;
use App\Models\Helpers\TaxCategory;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddress;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasRetinaSearch;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Fulfilment\PalletDelivery
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $ulid
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property int|null $warehouse_id
 * @property string|null $customer_reference
 * @property string $reference
 * @property PalletReturnTypeEnum $type Pallet|StoredItem
 * @property PalletReturnStateEnum $state
 * @property \Illuminate\Support\Carbon|null $in_process_at
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $picking_at
 * @property \Illuminate\Support\Carbon|null $picked_at
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property string|null $consolidated_at
 * @property \Illuminate\Support\Carbon|null $cancel_at
 * @property string|null $date
 * @property array|null $data
 * @property string|null $customer_notes
 * @property string|null $public_notes
 * @property string|null $internal_notes
 * @property int|null $delivery_address_id
 * @property int|null $collection_address_id
 * @property int $currency_id
 * @property string|null $grp_exchange
 * @property string|null $org_exchange
 * @property string $gross_amount Total asserts amount (excluding charges and shipping) before discounts
 * @property string $goods_amount
 * @property string $services_amount
 * @property string $net_amount
 * @property string|null $grp_net_amount
 * @property string|null $org_net_amount
 * @property int $tax_category_id
 * @property string $tax_amount
 * @property string $total_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property-read Address|null $address
 * @property-read Collection<int, Address> $addresses
 * @property-read Currency $currency
 * @property-read Customer|null $customer
 * @property-read Address|null $deliveryAddress
 * @property-read mixed $discount_amount
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @property-read Collection<int, \App\Models\Fulfilment\RecurringBill> $recurringBills
 * @property-read \App\Models\Helpers\RetinaSearch|null $retinaSearch
 * @property-read \App\Models\Fulfilment\PalletReturnStats|null $stats
 * @property-read Collection<int, \App\Models\Fulfilment\StoredItem> $storedItems
 * @property-read TaxCategory $taxCategory
 * @property-read Collection<int, \App\Models\Fulfilment\FulfilmentTransaction> $transactions
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletReturn withoutTrashed()
 * @mixin \Eloquent
 */

class PalletReturn extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasRetinaSearch;
    use HasAddress;
    use HasAddresses;

    protected $guarded = [];
    protected $casts   = [
        'state'              => PalletReturnStateEnum::class,
        'type'               => PalletReturnTypeEnum::class,
        'in_process_at'      => 'datetime',
        'submitted_at'       => 'datetime',
        'confirmed_at'       => 'datetime',
        'picking_at'         => 'datetime',
        'picked_at'          => 'datetime',
        'dispatched_at'      => 'datetime',
        'cancel_at'          => 'datetime',
        'data'               => 'array'
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function discountAmount(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->gross_amount - $this->net_amount
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function pallets(): BelongsToMany
    {
        return $this->belongsToMany(Pallet::class, 'pallet_return_items')->withPivot('state', 'id');
    }

    public function storedItems(): BelongsToMany
    {
        return $this->belongsToMany(StoredItem::class, 'pallet_return_items')->withPivot('state', 'id', 'pallet_stored_item_id');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PalletReturnStats::class);
    }

    public function recurringBills(): MorphToMany
    {
        return $this->morphToMany(RecurringBill::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(FulfilmentTransaction::class, 'parent');
    }

    public function services(): Collection
    {
        return $this->transactions()->where('type', 'service')->get();
    }

    public function products(): Collection
    {
        return $this->transactions()->where('type', 'product')->get();
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function taxCategory(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
