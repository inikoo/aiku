<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:52:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Actions\Marketing\Family\HydrateFamily;
use App\Actions\Marketing\Shop\HydrateShop;
use App\Models\Sales\SalesStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Marketing\Product
 *
 * @property int $id
 * @property string $composition
 * @property string|null $slug
 * @property int|null $shop_id
 * @property int|null $family_id
 * @property string|null $state
 * @property bool|null $status
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string $price unit price
 * @property string|null $pack units per pack
 * @property string|null $outer units per outer
 * @property string|null $carton units per carton
 * @property int|null $available
 * @property int|null $image_id
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Marketing\Family|null $family
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Marketing\HistoricProduct[] $historicRecords
 * @property-read int|null $historic_records_count
 * @property-read SalesStats|null $salesStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Marketing\TradeUnit[] $tradeUnits
 * @property-read int|null $trade_units_count
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|Product onlyTrashed()
 * @method static Builder|Product query()
 * @method static Builder|Product whereAvailable($value)
 * @method static Builder|Product whereCarton($value)
 * @method static Builder|Product whereCode($value)
 * @method static Builder|Product whereComposition($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereData($value)
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product whereFamilyId($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereImageId($value)
 * @method static Builder|Product whereName($value)
 * @method static Builder|Product whereOuter($value)
 * @method static Builder|Product wherePack($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereSettings($value)
 * @method static Builder|Product whereShopId($value)
 * @method static Builder|Product whereSlug($value)
 * @method static Builder|Product whereSourceId($value)
 * @method static Builder|Product whereState($value)
 * @method static Builder|Product whereStatus($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use SoftDeletes;

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    protected static function booted()
    {
        static::created(
            function (Product $product) {
                if($product->family_id){
                    HydrateFamily::make()->productsStats($product->family);
                }
                HydrateShop::make()->productStats($product->shop);
            }
        );
        static::deleted(
            function (Product $product) {
                HydrateFamily::make()->productsStats($product->family);
                HydrateShop::make()->productStats($product->shop);
            }
        );
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(TradeUnit::class)->withPivot('quantity')->withTimestamps();
    }

    public function salesStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope','sales');
    }

    public function historicRecords(): HasMany
    {
        return $this->hasMany(HistoricProduct::class);
    }
}
