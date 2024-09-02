<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 20:39:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Shipping\ShippingStateEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property int|null $shop_id
 * @property int|null $asset_id
 * @property bool $structural
 * @property bool $status
 * @property ShippingStateEnum $state
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string|null $price
 * @property string $units
 * @property string $unit
 * @property array $data
 * @property array $settings
 * @property int $currency_id
 * @property int|null $current_historic_asset_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property string|null $historic_source_id
 * @property-read \App\Models\Catalogue\Asset|null $asset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\Helpers\Currency $currency
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\Catalogue\HistoricAsset|null $historicAsset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Catalogue\HistoricAsset> $historicAssets
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\ShippingStats|null $stats
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Shipping withoutTrashed()
 * @mixin \Eloquent
 */
class Shipping extends Model implements Auditable
{
    use SoftDeletes;
    use HasUniversalSearch;
    use InAssetModel;
    use HasHistory;
    use HasSlug;

    protected $guarded = [];

    protected $casts = [
        'price'    => 'decimal:2',
        'state'    => ShippingStateEnum::class,
        'status'   => 'boolean',
        'data'     => 'array',
        'settings' => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function generateTags(): array
    {
        return [
            'catalogue',
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'description',
        'price',
        'currency_id',
        'units',
        'unit',
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

    public function stats(): HasOne
    {
        return $this->hasOne(ShippingStats::class);
    }

}
