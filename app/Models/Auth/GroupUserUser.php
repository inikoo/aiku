<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class GroupUserUser extends Pivot
{
    use UsesLandlordConnection;

    public $incrementing = true;

    protected $casts = [
        'data'            => 'array',

    ];

    protected $attributes = [
        'data'            => '{}',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
