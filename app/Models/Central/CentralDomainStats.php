<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\CentralDomainStats
 *
 * @property int $id
 * @property int $central_domain_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CentralDomainStats query()
 * @mixin \Eloquent
 */
class CentralDomainStats extends Model
{
    use UsesLandlordConnection;
}
