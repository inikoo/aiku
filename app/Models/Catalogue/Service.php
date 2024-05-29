<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:37:06 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Catalogue\Service
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property bool $status
 * @property ServiceStateEnum $state
 * @property int|null $shop_id
 * @property int|null $product_id
 * @property int $number_historic_outerables
 * @property string|null $auto_assign_action Used for auto assign this service to a action
 * @property string|null $auto_assign_action_type Used for auto assign this service to an action type
 * @property string|null $price
 * @property string|null $unit
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricOuterable> $historicRecords
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RecurringBill> $recurringBills
 * @property-read \App\Models\Catalogue\ServiceSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Service onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service query()
 * @method static \Illuminate\Database\Eloquent\Builder|Service withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Service withoutTrashed()
 * @mixin \Eloquent
 */
class Service extends Model
{
    use SoftDeletes;
    use HasUniversalSearch;
    use IsOuterable;

    protected $guarded = [];

    protected $casts = [
        'state'                  => ServiceStateEnum::class,
        'status'                 => 'boolean',
        'data'                   => 'array',

    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(ServiceSalesIntervals::class);
    }


    public function historicRecords(): MorphMany
    {
        return $this->morphMany(HistoricOuterable::class, 'outerable');
    }

    public function recurringBills(): MorphToMany
    {
        return $this->morphToMany(RecurringBill::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }
}
