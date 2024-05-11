<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 14:54:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\ShippingZoneSchema
 *
 * @property int $id
 * @property int $shop_id
 * @property bool $status
 * @property string $slug
 * @property string $name
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Catalogue\ShippingZone> $shippingZone
 * @method static \Database\Factories\Market\ShippingZoneSchemaFactory factory($count = null, $state = [])
 * @method static Builder|ShippingZoneSchema newModelQuery()
 * @method static Builder|ShippingZoneSchema newQuery()
 * @method static Builder|ShippingZoneSchema onlyTrashed()
 * @method static Builder|ShippingZoneSchema query()
 * @method static Builder|ShippingZoneSchema withTrashed()
 * @method static Builder|ShippingZoneSchema withoutTrashed()
 * @mixin Eloquent
 */
class ShippingZoneSchema extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;

    protected $casts = [
        'status'   => 'boolean',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function shippingZone(): HasMany
    {
        return $this->hasMany(ShippingZone::class);
    }
}
