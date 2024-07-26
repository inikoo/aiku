<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:55:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Accounting\Invoice;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Helpers\Currency;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InFulfilmentCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $reference
 * @property int $rental_agreement_id
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property RecurringBillStatusEnum|null $status
 * @property string $start_date
 * @property string|null $end_date
 * @property int $currency_id
 * @property string $grp_exchange
 * @property string $org_exchange
 * @property string $gross_amount Total asserts amount (excluding charges and shipping) before discounts
 * @property string $goods_amount
 * @property string $services_amount
 * @property string $rental_amount
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Invoice|null $invoices
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletDelivery> $palletDelivery
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\PalletReturn> $palletReturn
 * @property-read \App\Models\Fulfilment\RentalAgreement $rentalAgreement
 * @property-read \App\Models\Fulfilment\RecurringBillStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RecurringBillTransaction> $transactions
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill withoutTrashed()
 * @mixin \Eloquent
 */
class RecurringBill extends Model implements Auditable
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasSlug;
    use InFulfilmentCustomer;
    use HasHistory;

    protected $guarded = [];

    protected $casts = [
        'data'   => 'array',
        'status' => RecurringBillStatusEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function generateTags(): array
    {
        return ['fulfilment'];
    }

    protected array $auditInclude = [
        'reference',
        'status',
        'start_date',
        'end_date',
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
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function rentalAgreement(): BelongsTo
    {
        return $this->belongsTo(RentalAgreement::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(RecurringBillTransaction::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(RecurringBillStats::class);
    }

    public function palletDelivery(): MorphToMany
    {
        return $this->morphedByMany(PalletDelivery::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }

    public function palletReturn(): MorphToMany
    {
        return $this->morphedByMany(PalletReturn::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }

    public function invoices(): HasOne
    {
        return $this->hasOne(Invoice::class);

    }
}
