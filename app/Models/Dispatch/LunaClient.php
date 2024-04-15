<?php

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\Dispatch\LunaClient
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @method static \Illuminate\Database\Eloquent\Builder|LunaClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LunaClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LunaClient query()
 * @mixin \Eloquent
 */
class LunaClient extends Model
{
    use HasApiTokens;
}
