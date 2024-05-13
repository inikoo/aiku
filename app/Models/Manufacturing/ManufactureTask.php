<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 10:14:08 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InProduction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class ManufactureTask
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $production_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property string $task_materials_cost
 * @property string $task_energy_cost
 * @property string $task_other_cost
 * @property string $task_work_cost
 * @property bool $status
 * @property float $task_lower_target
 * @property float $task_upper_target
 * @property ManufactureTaskOperativeRewardTermsEnum $operative_reward_terms
 * @property ManufactureTaskOperativeRewardAllowanceTypeEnum $operative_reward_allowance_type
 * @property float $operative_reward_amount
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Manufacturing\Artifact> $artifacts
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Manufacturing\Production $production
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ManufactureTask withoutTrashed()
 * @mixin \Eloquent
 */

class ManufactureTask extends Model
{
    use InProduction;
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;

    protected $guarded = [];

    protected $casts   = [
        'data'                                => 'array',
        'operative_reward_terms'              => ManufactureTaskOperativeRewardTermsEnum::class,
        'operative_reward_allowance_type'     => ManufactureTaskOperativeRewardAllowanceTypeEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function artifacts()
    {
        return $this->belongsToMany(Artifact::class)->using(ArtifactManufactureTask::class);
    }
}
