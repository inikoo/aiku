<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Accounting;

use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Helpers\Address;
use App\Models\Helpers\Currency;
use App\Models\Helpers\UniversalSearch;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasRetinaSearch;
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
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property InvoiceTypeEnum $type
 * @property-read Address|null $address
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Address|null $billingAddress
 * @property-read Currency|null $currency
 * @property-read Customer|null $customer
 * @property-read Collection<int, Address> $fixedAddresses
 * @property-read Group|null $group
 * @property-read Collection<int, \App\Models\Accounting\InvoiceTransaction> $invoiceTransactions
 * @property-read Order|null $order
 * @property-read Collection<int, Order> $orders
 * @property-read Organisation|null $organisation
 * @property-read Collection<int, \App\Models\Accounting\Payment> $payments
 * @property-read RecurringBill|null $recurringBill
 * @property-read \App\Models\Helpers\RetinaSearch|null $retinaSearch
 * @property-read Shop|null $shop
 * @property-read \App\Models\Accounting\InvoiceStats|null $stats
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Accounting\InvoiceFactory factory($count = null, $state = [])
 * @method static Builder|Invoice newModelQuery()
 * @method static Builder|Invoice newQuery()
 * @method static Builder|Invoice onlyTrashed()
 * @method static Builder|Invoice query()
 * @method static Builder|Invoice withTrashed()
 * @method static Builder|Invoice withoutTrashed()
 * @mixin Eloquent
 */
class Invoice extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasRetinaSearch;
    use HasFactory;
    use InCustomer;
    use HasHistory;

    protected $casts = [
        'type'             => InvoiceTypeEnum::class,
        'data'             => 'array',
        'paid_at'          => 'datetime',
        'tax_liability_at' => 'datetime',
        'fetched_at'       => 'datetime',
        'last_fetched_at'  => 'datetime',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['accounting'];
    }

    protected array $auditInclude = [
        'reference',
        'type',
        'state',
        'status',
        'email',
        'phone',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    /**
     * Relation to main order, usually the only one, used no avoid looping over orders
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoiceTransactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(InvoiceStats::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function fixedAddresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'model', 'model_has_fixed_addresses')->withTimestamps();
    }

    public function payments(): MorphToMany
    {
        return $this->morphToMany(Payment::class, 'model', 'model_has_payments')->withTimestamps()->withPivot(['amount', 'share']);
    }

    public function recurringBill(): BelongsTo
    {
        return $this->belongsTo(RecurringBill::class);
    }

}
