<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-08h-54m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Web;

use App\Enums\Web\CustomerPoll\CustomerPollTypeEnum;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $shop_id
 * @property string $name
 * @property string $label
 * @property bool $in_registration
 * @property bool $in_registration_required
 * @property CustomerPollTypeEnum $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Web\CustomerPollOption> $options
 * @property-read \App\Models\Web\CustomerPollStat|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPoll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPoll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerPoll query()
 * @mixin \Eloquent
 */
class CustomerPoll extends Model
{
    use InShop;
    
    protected $casts = [
        'type' => CustomerPollTypeEnum::class
    ];

    protected $attributes = [
    ];

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(CustomerPollStat::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(CustomerPollOption::class);
    }
}
