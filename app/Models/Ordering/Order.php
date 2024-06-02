<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:26:20 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Catalogue\Shop;
use App\Models\Search\UniversalSearch;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasAddresses;
use App\Models\Traits\HasAttachments;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InCustomer;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Ordering\Order
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property int $shop_id
 * @property int $customer_id
 * @property int|null $customer_client_id
 * @property string|null $number
 * @property string|null $customer_number Customers own order number
 * @property OrderStateEnum $state
 * @property OrderStatusEnum $status
 * @property OrderHandingTypeEnum $handing_type
 * @property bool $customer_locked
 * @property bool $billing_locked
 * @property bool $delivery_locked
 * @property int|null $billing_address_id
 * @property int|null $delivery_address_id
 * @property int|null $collection_address_id
 * @property int|null $billing_country_id
 * @property int|null $delivery_country_id
 * @property Carbon $date
 * @property string|null $submitted_at
 * @property string|null $in_warehouse_at
 * @property string|null $handling_at
 * @property string|null $packed_at
 * @property string|null $finalised_at
 * @property string|null $dispatched_at
 * @property string|null $settled_at
 * @property string|null $cancelled_at
 * @property bool $is_invoiced
 * @property bool|null $is_picking_on_hold
 * @property bool|null $can_dispatch
 * @property string $items_discounts
 * @property string $items_net
 * @property int $currency_id
 * @property string $group_exchange
 * @property string $org_exchange
 * @property string $charges
 * @property string|null $shipping
 * @property string $net
 * @property string $tax
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Address> $addresses
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $attachments
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Address|null $billingAddress
 * @property-read Address|null $collectionAddress
 * @property-read \App\Models\CRM\Customer $customer
 * @property-read CustomerClient|null $customerClient
 * @property-read Address|null $deliveryAddress
 * @property-read Collection<int, DeliveryNote> $deliveryNotes
 * @property-read Collection<int, Address> $fixedAddresses
 * @property-read Group $group
 * @property-read Collection<int, Invoice> $invoices
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $media
 * @property-read Organisation $organisation
 * @property-read Collection<int, Payment> $payments
 * @property-read Shop $shop
 * @property-read \App\Models\Ordering\OrderStats|null $stats
 * @property-read Collection<int, \App\Models\Ordering\Transaction> $transactions
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Ordering\OrderFactory factory($count = null, $state = [])
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order query()
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 * @mixin Eloquent
 */
class Order extends Model implements HasMedia, Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;
    use HasFactory;
    use InCustomer;
    use HasAddresses;
    use HasAttachments;
    use HasHistory;


    protected $casts = [
        'data'         => 'array',
        'date'         => 'datetime',
        'state'        => OrderStateEnum::class,
        'status'       => OrderStatusEnum::class,
        'handing_type' => OrderHandingTypeEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('number')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function customerClient(): BelongsTo
    {
        return $this->belongsTo(CustomerClient::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class)->withTimestamps();
    }

    public function payments(): MorphToMany
    {
        return $this->morphToMany(Payment::class, 'model', 'model_has_payments')->withTimestamps()->withPivot(['amount', 'share']);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(OrderStats::class);
    }

    public function fixedAddresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'model', 'model_has_fixed_addresses')->withTimestamps();
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function collectionAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'model', 'model_has_addresses')->withTimestamps();
    }


}
