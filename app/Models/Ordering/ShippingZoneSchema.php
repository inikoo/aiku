<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:34:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

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
 * App\Models\Ordering\ShippingZoneSchema
 *
 * @property int $id
 * @property int $shop_id
 * @property bool $status
 * @property string $slug
 * @property string $name
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Ordering\ShippingZone> $shippingZone
 * @method static \Database\Factories\Ordering\ShippingZoneSchemaFactory factory($count = null, $state = [])
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
