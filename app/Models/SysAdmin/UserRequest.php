<?php
/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-11h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\SysAdmin;

use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRequest extends Model
{
    use InGroup;

    protected $guarded = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
