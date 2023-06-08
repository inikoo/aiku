<?php

namespace App\Models\Marketing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\Rent
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Rent newModelQuery()
 * @method static Builder|Rent newQuery()
 * @method static Builder|Rent query()
 * @mixin Eloquent
 */
class Rent extends Model
{
    use UsesTenantConnection;
}
