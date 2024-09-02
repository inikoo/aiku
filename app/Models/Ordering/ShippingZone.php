<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:34:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Ordering;

use App\Models\Traits\HasHistory;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Ordering\ShippingZone
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property int $shipping_zone_schema_id
 * @property bool $status
 * @property string $slug
 * @property bool $is_failover
 * @property string $code
 * @property string $name
 * @property array $price
 * @property array $territories
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\Shop $shop
 * @property-read \App\Models\Ordering\ShippingZoneStats|null $stats
 * @method static \Database\Factories\Ordering\ShippingZoneFactory factory($count = null, $state = [])
 * @method static Builder|ShippingZone newModelQuery()
 * @method static Builder|ShippingZone newQuery()
 * @method static Builder|ShippingZone onlyTrashed()
 * @method static Builder|ShippingZone query()
 * @method static Builder|ShippingZone withTrashed()
 * @method static Builder|ShippingZone withoutTrashed()
 * @mixin Eloquent
 */
class ShippingZone extends Model implements Auditable
{
    use SoftDeletes;
    use InShop;
    use HasSlug;
    use HasFactory;
    use HasHistory;

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

    public function generateTags(): array
    {
        return ['ordering'];
    }

    protected array $auditInclude = [
        'name',
        'type',
    ];

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

    public function stats(): HasOne
    {
        return $this->hasOne(ShippingZoneStats::class);
    }


}
