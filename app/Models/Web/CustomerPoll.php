<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-08h-54m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Web;

use App\Enums\Web\CustomerPoll\CustomerPollTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerPoll extends Model
{
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
