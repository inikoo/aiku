<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:55:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InFulfilmentCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property string|null $reference
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property RentalAgreementStateEnum $state
 * @property RentalAgreementBillingCycleEnum $billing_cycle
 * @property int|null $pallets_limit Agreed max number pallets space allocated
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read Group $group
 * @property-read Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\RecurringBill> $recurringBills
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement query()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|RentalAgreement withoutTrashed()
 * @mixin \Eloquent
 */
class RentalAgreement extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use HasSlug;
    use InFulfilmentCustomer;

    protected $guarded = [];
    protected $casts   = [
        'data'          => 'array',
        'state'         => RentalAgreementStateEnum::class,
        'billing_cycle' => RentalAgreementBillingCycleEnum::class
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

    public function recurringBills(): HasMany
    {
        return $this->hasMany(RecurringBill::class);
    }


}
