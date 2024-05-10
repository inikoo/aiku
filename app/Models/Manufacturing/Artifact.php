<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 11:29:06 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\Artifact\ArtifactStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InProduction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $stock_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property int|null $stock_family_id
 * @property ArtifactStateEnum $state
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\ManufactureTask> $manufactureTasks
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Manufacturing\Production|null $production
 * @property-read \App\Models\Manufacturing\ArtifactStats|null $stats
 * @property-read Stock $stock
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact query()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Artifact withoutTrashed()
 * @mixin \Eloquent
 */
class Artifact extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use InProduction;

    protected $casts = [
        'data'                   => 'array',
        'settings'               => 'array',
        'state'                  => ArtifactStateEnum::class
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(ArtifactStats::class);
    }

    public function manufactureTasks()
    {
        return $this->belongsToMany(ManufactureTask::class)->using(ArtifactManufactureTask::class);
    }


}
