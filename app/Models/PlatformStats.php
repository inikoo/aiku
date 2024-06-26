<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $platform_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlatformStats query()
 * @mixin \Eloquent
 */
class PlatformStats extends Model
{
}
