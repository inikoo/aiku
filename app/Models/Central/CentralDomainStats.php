<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\CentralDomainStats
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats query()
 * @mixin \Eloquent
 */
class CentralDomainStats extends Model
{
    use UsesLandlordConnection;
}
