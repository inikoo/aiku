<?php

namespace App\Models\SysAdmin;

use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Manufacturing\Production;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskProductionStat extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts   = [
        'operative_reward_terms'              => ManufactureTaskOperativeRewardTermsEnum::class,
        'operative_reward_allowance_type'     => ManufactureTaskOperativeRewardAllowanceTypeEnum::class,
    ];

    public function task() : BelongsTo {

        return $this->belongsTo(Task::class);
        
    }
    public function production() : BelongsTo {

        return $this->belongsTo(Production::class);

    }
}
