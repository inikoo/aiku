<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Billable\BillableStateEnum;
use App\Enums\Catalogue\Billable\BillableTypeEnum;
use App\Enums\Catalogue\Billable\BillableUnitRelationshipType;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\Rental;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Helpers\Currency;
use App\Models\Search\UniversalSearch;
use App\Models\Studio\Media;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\Billable
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $slug
 * @property BillableTypeEnum $type
 * @property string $owner_type
 * @property int $owner_id
 * @property string $parent_type
 * @property int $parent_id
 * @property string $outerable_type
 * @property int|null $current_historic_outerable_id
 * @property BillableStateEnum $state
 * @property bool $status
 * @property BillableUnitRelationshipType|null $unit_relationship_type
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $main_outerable_id
 * @property string|null $main_outerable_price main outer price
 * @property int $currency_id
 * @property string|null $main_outerable_unit
 * @property int|null $main_outerable_available_quantity
 * @property int|null $image_id
 * @property string|null $rrp RRP per outer
 * @property array $settings
 * @property array $data
 * @property int|null $family_id
 * @property int|null $department_id
 * @property bool $is_legacy
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read Collection<int, Barcode> $barcode
 * @property-read Currency $currency
 * @property-read \App\Models\Catalogue\HistoricOuterable|null $currentHistoricOuterable
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Catalogue\HistoricOuterable> $historicOuters
 * @property-read Media|null $image
 * @property-read MediaCollection<int, Media> $images
 * @property-read Model|\Eloquent $mainOuterable
 * @property-read MediaCollection<int, Media> $media
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Catalogue\Outer> $outers
 * @property-read Collection<int, RecurringBill> $recurringBills
 * @property-read Rental|null $rental
 * @property-read \App\Models\Catalogue\BillableSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Service|null $service
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\BillableStats|null $stats
 * @property-read Collection<int, TradeUnit> $tradeUnits
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\Catalogue\ProductFactory factory($count = null, $state = [])
 * @method static Builder|Billable newModelQuery()
 * @method static Builder|Billable newQuery()
 * @method static Builder|Billable onlyTrashed()
 * @method static Builder|Billable query()
 * @method static Builder|Billable withTrashed()
 * @method static Builder|Billable withoutTrashed()
 * @mixin Eloquent
 */
class Billable extends Model implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;
    use InShop;
    use HasImage;

    protected $casts = [
        'data'                   => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
        'type'                   => BillableTypeEnum::class,
        'state'                  => BillableStateEnum::class,
        'unit_relationship_type' => BillableUnitRelationshipType::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->shop->slug.'-'.$this->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    protected $guarded = [];

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(BillableSalesIntervals::class);
    }

    public function historicOuters(): HasMany
    {
        return $this->hasMany(HistoricOuterable::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(BillableStats::class);
    }

    public function barcode(): MorphToMany
    {
        return $this->morphToMany(Barcode::class, 'model', 'model_has_barcode')->withTimestamps();
    }

    public function outers(): HasMany
    {
        return $this->hasMany(Outer::class);
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class, 'id', 'main_outerable_id');
    }

    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class, 'id', 'main_outerable_id');
    }

    public function mainOuterable(): MorphTo
    {
        return $this->morphTo(type: 'outerable_type', id: 'main_outerable_id');
    }

    public function currentHistoricOuterable(): BelongsTo
    {
        return $this->belongsTo(HistoricOuterable::class);
    }

    public function recurringBills(): MorphToMany
    {
        return $this->morphToMany(RecurringBill::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

}
