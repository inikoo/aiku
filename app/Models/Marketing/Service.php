<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 16:44:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use App\Models\Sales\SalesStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\Service
 *
 * @property int $id
 * @property string|null $slug
 * @property int|null $current_historic_service_id
 * @property int|null $shop_id
 * @property bool|null $status
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string $price unit price
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\HistoricService> $historicRecords
 * @property-read SalesStats|null $salesStats
 * @property-read \App\Models\Marketing\Shop|null $shop
 * @property-read \App\Models\Marketing\ServiceStats|null $stats
 * @method static Builder|Service newModelQuery()
 * @method static Builder|Service newQuery()
 * @method static Builder|Service onlyTrashed()
 * @method static Builder|Service query()
 * @method static Builder|Service withTrashed()
 * @method static Builder|Service withoutTrashed()
 * @mixin \Eloquent
 */
class Service extends Model
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;

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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function salesStats(): MorphOne
    {
        return $this->morphOne(SalesStats::class, 'model')->where('scope','sales');
    }

    public function historicRecords(): HasMany
    {
        return $this->hasMany(HistoricService::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ServiceStats::class);
    }
}
