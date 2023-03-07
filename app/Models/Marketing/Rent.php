<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\Rent
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Rent newModelQuery()
 * @method static Builder|Rent newQuery()
 * @method static Builder|Rent query()
 * @mixin \Eloquent
 */
class Rent extends Model
{
    use UsesTenantConnection;
}
