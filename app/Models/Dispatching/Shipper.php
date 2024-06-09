<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use App\Models\Helpers\Issue;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Dispatching\Shipper
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $code
 * @property string|null $api_shipper
 * @property bool $status
 * @property string $name
 * @property string|null $contact_name
 * @property string|null $company_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $tracking_url
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, Issue> $issues
 * @property-read Organisation $organisation
 * @property-read Collection<int, \App\Models\Dispatching\Shipment> $shipments
 * @property-read Collection<int, \App\Models\Dispatching\ShippingEvent> $shippingEvents
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @method static Builder|Shipper newModelQuery()
 * @method static Builder|Shipper newQuery()
 * @method static Builder|Shipper onlyTrashed()
 * @method static Builder|Shipper query()
 * @method static Builder|Shipper withTrashed()
 * @method static Builder|Shipper withoutTrashed()
 * @mixin Eloquent
 */
class Shipper extends Model
{
    use HasSlug;
    use SoftDeletes;
    use HasUniversalSearch;

    use HasFactory;

    protected $casts = [
        'data'   => 'array',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function issues(): MorphToMany
    {
        return $this->morphToMany(Issue::class, 'issuable');
    }

    public function shippingEvents(): MorphToMany
    {
        return $this->morphToMany(ShippingEvent::class, 'provider');
    }
}
