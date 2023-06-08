<?php

namespace App\Models\Marketing;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Marketing\HistoricRent
 *
 * @property int $id
 * @property int $rent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|HistoricRent newModelQuery()
 * @method static Builder|HistoricRent newQuery()
 * @method static Builder|HistoricRent query()
 * @mixin Eloquent
 */
class HistoricRent extends Model
{
    use UsesTenantConnection;
}
