<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 14:54:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Marketing\ShippingZoneSchema
 *
 * @property int $id
 * @property int $shop_id
 * @property bool $status
 * @property string $slug
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Marketing\ShippingZone> $shippingZone
 * @method static \Database\Factories\Marketing\ShippingZoneSchemaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingZoneSchema withoutTrashed()
 * @mixin \Eloquent
 */
class ShippingZoneSchema extends Model
{
    use UsesTenantConnection;
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
