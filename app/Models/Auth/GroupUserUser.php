<?php

namespace App\Models\Auth;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Auth\GroupUserUser
 *
 * @property int $id
 * @property int $group_user_id
 * @property int $user_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Auth\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|GroupUserUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupUserUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupUserUser query()
 * @mixin Eloquent
 */
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
