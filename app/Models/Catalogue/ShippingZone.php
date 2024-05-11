<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 14:55:08 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Catalogue\ShippingZone
 *
 * @property int $id
 * @property int $shop_id
 * @property int $shipping_zone_schema_id
 * @property bool $status
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $territories
 * @property array $price
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Database\Factories\Ordering\ShippingZoneFactory factory($count = null, $state = [])
 * @method static Builder|ShippingZone newModelQuery()
 * @method static Builder|ShippingZone newQuery()
 * @method static Builder|ShippingZone onlyTrashed()
 * @method static Builder|ShippingZone query()
 * @method static Builder|ShippingZone withTrashed()
 * @method static Builder|ShippingZone withoutTrashed()
 * @mixin Eloquent
 */
class ShippingZone extends Model
{
    use SoftDeletes;

    use HasSlug;
    use HasFactory;


    protected $casts = [
        'territories' => 'array',
        'price'       => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'territories' => '{}',
        'price'       => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
