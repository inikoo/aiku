<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:24:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\CRM\Customer;
use App\Models\Helpers\TaxCategory;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasRetinaSearch;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property int $number_pallets
 * @property int $number_pallet_stored_items
 * @property int $number_stored_items
 * @property PalletDeliveryStateEnum $state
 * @property \Illuminate\Support\Carbon|null $in_process_at
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property \Illuminate\Support\Carbon|null $not_received_at
 * @property \Illuminate\Support\Carbon|null $booking_in_at
 * @property \Illuminate\Support\Carbon|null $booked_in_at
 * @property int|null $delivery_address_id
 * @property int|null $collection_address_id
 * @property string $handing_type
 * @property \Illuminate\Support\Carbon|null $estimated_delivery_date
 * @property \Illuminate\Support\Carbon|null $date
 * @property string|null $customer_notes
 * @property string|null $public_notes
 * @property string|null $internal_notes
 * @property int $currency_id
 * @property string $grp_exchange
 * @property string $org_exchange
 * @property string $gross_amount Total asserts amount (excluding charges and shipping) before discounts
 * @property string $goods_amount
 * @property string $services_amount
 * @property string $net_amount
 * @property string $grp_net_amount
 * @property string $org_net_amount
 * @property int $tax_category_id
 * @property string $tax_amount
 * @property string $total_amount
 * @property string $payment_amount
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\Pallet> $pallets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RecurringBill> $recurringBills
 * @property-read \App\Models\Helpers\RetinaSearch|null $retinaSearch
 * @property-read \App\Models\Fulfilment\PalletDeliveryStats|null $stats
 * @property-read TaxCategory $taxCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\FulfilmentTransaction> $transactions
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery query()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletDelivery withoutTrashed()
 * @mixin \Eloquent
 */
class PalletDelivery extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasRetinaSearch;

    protected $guarded = [];
    protected $casts   = [
        'state'                   => PalletDeliveryStateEnum::class,
        'in_process_at'           => 'datetime',
        'submitted_at'            => 'datetime',
        'confirmed_at'            => 'datetime',
        'received_at'             => 'datetime',
        'not_received_at'         => 'datetime',
        'booked_in_at'            => 'datetime',
        'booking_in_at'           => 'datetime',
        'dispatched_at'           => 'datetime',
        'date'                    => 'datetime',
        'estimated_delivery_date' => 'datetime:Y-m-d',
        'data'                    => 'array'
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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function pallets(): HasMany
    {
        return $this->hasMany(Pallet::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PalletDeliveryStats::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(FulfilmentTransaction::class, 'parent');
    }

    public function services()
    {
        $transactions = $this->transactions()->where('type', 'service')->get();

        return $transactions;
    }

    public function products()
    {
        $transactions = $this->transactions()->where('type', 'product')->get();

        return $transactions;
    }

    public function taxCategory(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class);
    }

    public function recurringBills(): MorphToMany
    {
        return $this->morphToMany(RecurringBill::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }
}
