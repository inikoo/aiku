<?php

namespace App\Models\Central;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

/**
 * App\Models\Central\CentralDomainStats
 *
 * @property int $id
 * @property int $central_domain_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CentralDomainStats newModelQuery()
 * @method static Builder|CentralDomainStats newQuery()
 * @method static Builder|CentralDomainStats query()
 * @mixin Eloquent
 */
class CentralDomainStats extends Model
{
    use UsesLandlordConnection;
}
