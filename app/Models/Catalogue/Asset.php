<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\Rental;
use App\Models\Helpers\Barcode;
use App\Models\Helpers\Currency;
use App\Models\Traits\HasImage;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $slug
 * @property AssetTypeEnum $type
 * @property string $model_type
 * @property int|null $model_id
 * @property int|null $current_historic_asset_id
 * @property AssetStateEnum $state
 * @property bool $status
 * @property string $code mirror of asset model
 * @property string|null $name mirror of asset model
 * @property string|null $price mirror of asset model
 * @property int $number_units mirror of asset model
 * @property string|null $unit mirror of asset model
 * @property int $currency_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property ProductUnitRelationshipType $unit_relationship_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Barcode> $barcode
 * @property-read Currency $currency
 * @property-read \App\Models\Catalogue\HistoricAsset|null $currentHistoricOuterable
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricAsset> $historicAssets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricAsset> $historicOuters
 * @property-read \App\Models\Studio\Media|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $images
 * @property-read Model|\Eloquent $mainOuterable
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Studio\Media> $media
 * @property-read Model|\Eloquent $model
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RecurringBill> $recurringBills
 * @property-read Rental|null $rental
 * @property-read \App\Models\Catalogue\AssetSalesIntervals|null $salesIntervals
 * @property-read \App\Models\Catalogue\Service|null $service
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\AssetStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset query()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Asset withoutTrashed()
 * @mixin \Eloquent
 */
class Asset extends Model implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;
    use InShop;
    use HasImage;

    protected $casts = [
        'data'                   => 'array',
        'status'                 => 'boolean',
        'type'                   => AssetTypeEnum::class,
        'state'                  => AssetStateEnum::class,
        'unit_relationship_type' => ProductUnitRelationshipType::class
    ];

    protected $attributes = [
        'data'     => '{}',
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

    public function stats(): HasOne
    {
        return $this->hasOne(AssetStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(AssetSalesIntervals::class);
    }

    public function historicOuters(): HasMany
    {
        return $this->hasMany(HistoricAsset::class);
    }


    public function barcode(): MorphToMany
    {
        return $this->morphToMany(Barcode::class, 'model', 'model_has_barcode')->withTimestamps();
    }

    public function historicAssets(): HasMany
    {
        return $this->hasMany(HistoricAsset::class);
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
        return $this->belongsTo(HistoricAsset::class);
    }

    public function recurringBills(): MorphToMany
    {
        return $this->morphToMany(RecurringBill::class, 'model', 'model_has_recurring_bills')->withTimestamps();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

}
