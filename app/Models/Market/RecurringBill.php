<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Apr 2024 17:12:30 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property string|null $status
 * @property string $start_date
 * @property string $end_date
 * @property string $amount
 * @property string $tax
 * @property string $total
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Fulfilment $fulfilment
 * @property-read FulfilmentCustomer $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \App\Models\Market\RentalAgreement $rentalAgreement
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RecurringBill withoutTrashed()
 * @mixin \Eloquent
 */
class RecurringBill extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasSlug;

    protected $guarded = [];
    protected $casts   = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);

    }

    public function fulfilmentCustomer(): BelongsTo
    {
        return $this->belongsTo(FulfilmentCustomer::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function rentalAgreement(): BelongsTo
    {
        return $this->belongsTo(RentalAgreement::class);

    }



}
