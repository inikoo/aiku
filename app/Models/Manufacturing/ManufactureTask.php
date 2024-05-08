<?php

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class ManufactureTask
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property float $task_materials_cost
 * @property float $task_energy_cost
 * @property float $task_other_cost
 * @property float $task_work_cost
 * @property \Carbon\Carbon $task_from
 * @property \Carbon\Carbon $task_to
 * @property bool $task_active
 * @property float $task_lower_target
 * @property float $task_upper_target
 * @property ManufactureTaskOperativeRewardTermsEnum $operative_reward_terms
 * @property ManufactureTaskOperativeRewardAllowanceTypeEnum $operative_reward_allowance_type
 * @property float $operative_reward_amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 */

class ManufactureTask extends Model
{
    use InOrganisation;
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;

    protected $guarded = [];

    protected $casts   = [
        'task_from'                           => 'datetime',
        'task_to'                             => 'datetime',
        'operative_reward_terms'              => ManufactureTaskOperativeRewardTermsEnum::class,
        'operative_reward_allowance_type'     => ManufactureTaskOperativeRewardAllowanceTypeEnum::class,
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
}
