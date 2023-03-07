<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\CentralDomainStats
 *
 * @property int $id
 * @property int $central_domain_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|CentralDomainStats newModelQuery()
 * @method static Builder|CentralDomainStats newQuery()
 * @method static Builder|CentralDomainStats query()
 * @mixin \Eloquent
 */
class CentralDomainStats extends Model
{
    use UsesLandlordConnection;
}
