<?php

namespace App\Models\Manufacturing;

use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ManufactureTask
 * @property int $id
 * @property int $key
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
 * @property string $operative_reward_terms
 * @property string $operative_reward_allowance_type
 * @property float $operative_reward_amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */

class ManufactureTask extends Model
{
    use InOrganisation;
    protected $guarded = [];
    protected $casts   = [
        'task_from'                           => 'datetime',
        'task_to'                             => 'datetime',
        'operative_reward_terms'              => ManufactureTaskOperativeRewardTermsEnum::class,
        'operative_reward_allowance_type'     => ManufactureTaskOperativeRewardAllowanceTypeEnum::class,
    ];
}
