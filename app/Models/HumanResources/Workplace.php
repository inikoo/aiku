<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 23:30:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Actions\Utils\Abbreviate;
use App\Models\Assets\Timezone;
use App\Models\ClockingMachine;
use App\Models\Traits\HasTenantAddress;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\Workplace
 *
 * @property int $id
 * @property bool $status
 * @property string $type
 * @property string $slug
 * @property string $name
 * @property int|null $timezone_id
 * @property int|null $address_id
 * @property array $data
 * @property array $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ClockingMachine> $clockingMachines
 * @property-read Model|\Eloquent $owner
 * @property-read Timezone|null $timezone
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|Workplace newModelQuery()
 * @method static Builder|Workplace newQuery()
 * @method static Builder|Workplace onlyTrashed()
 * @method static Builder|Workplace query()
 * @method static Builder|Workplace withTrashed()
 * @method static Builder|Workplace withoutTrashed()
 * @mixin \Eloquent
 */
class Workplace extends Model
{
    use UsesTenantConnection;
    use HasSlug;
    use HasUniversalSearch;
    use SoftDeletes;
    use HasTenantAddress;

    protected $casts = [
        'data'        => 'array',
        'location'    => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'data'        => '{}',
        'location'    => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return Abbreviate::run($this->name, digits: true, maximumLength: 8);
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(8);
    }

    public function timezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function clockingMachines(): HasMany
    {
        return $this->hasMany(ClockingMachine::class);
    }

}
